<?php
/**
  * Attach to any model to creating drafts of the model.
  *
  * To determine the User making the save:
  *   uses the $Model->getUserId() if the method exists (could implement in AppModel)
  *   uses the AuthComponent::user() if the class exists (App::uses('AuthComponent', 'Controller/Component'))
  *   else, user_id = null
  *
  * Setup:
  *   You have to install the drafts table into your database.  You can do so by running:
  *
  * cake schema create -p Draft
  *
  *
  * Example Usage:
  * @example
  * public $actsAs = array('Draft.Draftable');
  *
  * @example
  	public $actsAs = array('Draft.Draftable' => array(
  		'bind' => true, //attach Draft as HasMany relationship for you onFind and if contained (false by default)
  		'modified_field' => 'updated' //modified field is updated, used in cleanup. (modified by default)
  	));

  	Save A Draft
  	@example
  	$data = array(
  		'Model' => array(
  			'field1' => 'value',
  		)
  	);
  	$this->Model->save($data, array('draft' => true)); //saves the data as a draft
  	@example
  	$data = array(
  		'Model' => array(
  			'draft' => true,
  			'field1' => 'value',
  		)
  	);
  	$this->Model->save($data); //will save as draft because 'draft' is set to true in $data['Model']
  	@example
  	$this->Model->data = array(
  		'Model' => array(
  			'field1' => 'value',
  		)
  	); 
  	$this->Model->saveDraft(); //will save any data in $Model->data as draft.

  	Find Draft
  	@example
  	$data = $this->Model->find('first', array('draft' => true));
  	$this->request->data = $this->Model->mergeDraft($data);
  	@example
  	$this->Model->bindDraft();
  	$data = $this->Model->find('first', array('contain' => array('Draft')));
  	$this->request->data = $this->Model->mergeDraft($data);
  	@example
  	$this->request->data = $this->Model->findDraft(array(
  		'conditions' => array(
  		 	//your model conditions
  		),
  		'contain' => array(
  			//Your contains
  		),
  	));

  	Find Unsaved record draft for user
  	@example
  	$this->request->data = $this->Model->findDraftByUser(); //Find draft by logged in user
  	$this->request->data = $this->Model->findDraftByUser(1); //Find draft by logged in user_id 1

  	Delete a Draft
  	@example
  	$this->Model->deleteDraft(); //will delete any draft data saved to $Model->id;

  * @version: since 1.0
  * @author: Nick Baker
  * @link: http://www.webtechnick.com
  */
App::import('Component', 'Auth');
App::uses('Hash','Utility');
//App::uses('AuthComponent', 'Controller/Component');
class DraftableBehavior extends ModelBehavior {
	public $Draft = null;
	public $errors = array();
	/**
	* Setup the behavior
	* @param Model model
	* @param array of settings
	*/
	public function setUp(Model $Model, $settings = array()) {
		$settings = array_merge(array(
			'bind' => false,
			'modified_field' => 'modified',
		), (array)$settings);
		if (!$Model->Behaviors->attached('Containable')) {
			$Model->Behaviors->attach('Containable');
		}
		$this->settings[$Model->alias] = $settings;
		$this->Draft = ClassRegistry::init('Draft.Draft');
	}

	/**
	* On save will version the current state of the model with containable settings.
	* @param Model model
	*/
	public function beforeSave(Model $Model, $options = array()){
		if (array_key_exists('draft', $options) || !empty($Model->data[$Model->alias]['draft'])) {
			$this->saveDraft($Model); //we're saving a draft, not the actual record.
			return false; //to hault the normal draft save
		} else {
			return $Model->beforeSave(); //if we're not saving a draft, save it normally.
		}
	}

	/**
	* After save, will delete any draft data for the id and model.
	* @param Model model
	*/
	public function afterSave(Model $Model, $created = false, $options = array()) {
		$this->deleteDraft($Model);
		return $Model->afterSave($created);
	}

	/**
	* Add the association on find if they turn on the association in settings
	* Only adds the bind if contained in the query
	* @param Model model
	* @param array query
	* @return result of Model beforeFind
	*/
	public function beforeFind(Model $Model, $query = array()){
		if (!empty($query['draft'])) {
			//bind if we explicicty ask for it.
			$this->bindDraft($Model);
		} elseif (isset($query['contain']) && $query['contain'] == 'Draft') {
			//Special case
			$this->bindDraft($Model);
		} elseif (isset($query['contain']) && is_array($query['contain']) && (key_exists('Draft', $query['contain']) || in_array('Draft', $query['contain']))) {
			//bind if we ask for it in contain, regardless of autobind.
			$this->bindDraft($Model);
		} elseif ($this->settings[$Model->alias]['bind'] && !isset($query['contain'])) {
			//Only auto contain and if auto is true and no contain is specified
			$this->bindDraft($Model);
		}
		return $Model->beforeFind($query);
	}

	/**
	* Bind the icing model on demand, this is useful right before a call in which you want to contain
	* but don't have the association.
	* @param Model model
	* @return result of bindModel
	*/
	public function bindDraft(Model $Model, $reset = true){
		return $Model->bindModel(array(
			'hasOne' => array(
				'Draft' => array(
					'className' => 'Draft.Draft',
					'foreignKey' => 'model_id',
					'conditions' => array("Draft.model" => $Model->alias)
				)
			)
		), $reset);
	}

	/**
	* Version the delete, mark as deleted in Versionable
	* @param Model model
	* @param boolean cascade
	*/
	public function beforeDelete(Model $Model, $cascade = true){
		$this->deleteDraft($Model);
		return $Model->beforeDelete($cascade);
	}

	/**
	* Find and merge the draft data into the data returned from the record.
	* @param Model model
	* @param array options
	* @return result of find.
	*/
	public function findDraft(Model $Model, $options = array()) {
		$this->bindDraft($Model);
		if (isset($options['contain'])) {
			$options['contain'][] = 'Draft'; //Add draft to it if we don't have it.
		}
		$options = array_merge(array(
			'draft' => true,
			'contain' => array('Draft')
		), (array) $options);
		$data = $Model->find('first', $options);

		return $this->mergeDraft($Model, $data);
	}
	
	/**
	* Merge the returned draft into the return value.
	* @param Model model
	* @param array of data
	* @return data
	*/
	public function mergeDraft(Model $Model, $data) {
		if (!isset($data[$Model->alias]) || empty($data)) {
			return $data;
		}
		
		if (!empty($data['Draft']['id'])) {
			//Copy original record into it's ModelOriginal key.
			$data[$Model->alias . 'Original'] = $data[$Model->alias];
			//Grab and merge the draft data into the alias.
			$draft_data = json_decode($data['Draft']['json'], true);
			$data = Hash::merge($data, $draft_data);
			$data[$Model->alias]['is_draft'] = true;
		}
		return $data;
	}

	/**
	* Find a draft by the logged in user.
	* @param Model model
	* @param array of options
	* @return non-saved data of drafts
	*/
	public function findDraftByUser(Model $Model, $userId = null) {
		if ($userId === null) {
			$userId = $this->userIdForDraft($Model);
		}
		$draft = $this->Draft->find('first', array(
			'conditions' => array(
				'user_id' => $userId,
				'model' => $Model->alias,
				'model_id' => null
			),
			'order' => array('Draft.modified DESC')
		));
		$retval = array();
		if (!empty($draft)) {
			$retval = json_decode($draft['Draft']['json'], true);
			$retval[$Model->alias]['is_draft'] = true;
			$retval['Draft'] = $draft['Draft'];
		}
		return $retval;
	}

	/**
	* Goes through the list of drafts and deletes anything that is modified before the
	* records saved modified date (ie, it's been saved).
	* @param Model model
	* @return mixed int of drafts cleared, or false if no modified or updated field is detected on model
	*/
	public function cleanupDrafts(Model $Model) {
		if (!$Model->hasField($this->settings[$Model->alias]['modified_field'])) {
			return false;
		}
		$drafts = $this->Draft->find('all', array(
			'fields' => array('Draft.modified','Draft.model','Draft.model_id','Draft.id'),
			'conditions' => array('Draft.model_id !=' => null, 'Draft.model' => $Model->alias)
		));
		$count = 0;
		foreach ($drafts as $draft) {
			if ($Model) {
				$Model->id = $draft['Draft']['model_id'];
				if (strtotime($draft['Draft']['modified']) < strtotime($Model->field($this->settings[$Model->alias]['modified_field']))) {
					$count++;
					$this->Draft->delete($draft['Draft']['id']);
				}
			}
		}
		return $count;
	}

	/**
	* Get the version data from the Model based on settings and deleting
	* this is used in beforeDelete and beforeSave
	* @param Model model
	* @param boolean deleted
	*/
	public function saveDraft(Model $Model, $data = array()){
		if ($data === array()) {
			$data = $Model->data;
		}
		$model_id = null;
		if ($Model->id) {
			$model_id = $Model->id;
		}
		if (isset($data[$Model->alias][$Model->primaryKey]) && !empty($data[$Model->alias][$Model->primaryKey])) {
			$model_id = $data[$Model->alias][$Model->primaryKey];
		}
		$draft_data = array(
			'user_id' => $this->userIdForDraft($Model),
			'model_id' => $model_id,
			'model' => $Model->alias,
			'json' => json_encode($data)
		);
		return $this->Draft->saveDraft($draft_data, $this->settings[$Model->alias]);
	}

	/**
	* Delete all the draft data saved for a paticular record
	* @param Model model
	* @return boolean success
	*/
	public function deleteDraft(Model $Model, $id = null) {
		if ($id === null && $Model->id) {
			$id = $Model->id;
		}
		$conditions = array(
			'model' => $Model->alias,
			'model_id' => $id,
		);
		if ($this->Draft->hasAny($conditions)) {
			return $this->Draft->deleteAll($conditions);
		}
		return true;
	}

	/**
	* Grab the userID from the AuthComponent or getUserId in the model somewhere.
	* @param Model model
	* @return mixed int of ID or null
	*/
	private function userIdForDraft(Model $Model) {
		if (method_exists($Model, 'getUserId')) {
			return $Model->getUserId();
		} elseif (class_exists('AuthComponent') && class_exists('CakeSession') && CakeSession::started()) {
			return AuthComponent::user('id');
		}
		return null;
	}

	/**
	* Adds error text to errors array
	* @param Model model
	* @param string message to append
	*/
	private function addError(Model $Model, $message){
		if(!isset($this->errors[$Model->alias])){
			$this->errors[$Model->alias] = array();
		}
		$this->errors[$Model->alias][] = $message;
	}
}
