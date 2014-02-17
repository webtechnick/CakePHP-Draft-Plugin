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
  		'bind'            => true,          //attach Draft as HasMany relationship for you onFind and if contained
  	));

  	Save A Draft
  	@example
  	$this->Model->save($data, array('draft' => true)); //saves the data as a draft
  	$data = array(
  		'Model' => array(
  			'draft' => true,
  			'field1' => 'value',
  			'field2' => 'value',
  		)
  	);
  	$this->Model->save($data); //will save as draft because 'draft' is set to true in $data['Model']

  	Find Draft
  	@example
  	$this->request->data = $this->Model->findDraft(array(
  		'conditions' => array(
  		 //your model conditions
  		)
  	));
  	
  	Find Unsaved record draft for user
  	@example
  	$this->request->data = $this->Model->findDraftByUser();

  * @version: since 1.0
  * @author: Nick Baker
  * @link: http://www.webtechnick.com
  */
App::import('Component', 'Auth');
//App::uses('AuthComponent', 'Controller/Component');
class DraftableBehavior extends ModelBehavior {
	public $Draft = null;
	public $errors = array();
	/**
	* Setup the behavior
	*/
	public function setUp(Model $Model, $settings = array()){
		$settings = array_merge(array(
			'bind' => false
		), (array)$settings);
		if(!$Model->Behaviors->attached('Containable')){
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
			return $this->saveDraft($Model); //we're saving a draft, not the actual record.
		} else {
			return $Model->beforeSave(); //if we're not saving a draft, save it normally.
		}
	}

	/**
	* Add the association on find if they turn on the association in settings
	* Only adds the bind if contained in the query
	* @param Model model
	* @param array query
	*/
	public function beforeFind(Model $Model, $query = array()){
		if ($this->settings[$Model->alias]['bind']) {
			//Only contain if we have it contained, or if no contain is specified
			if (!isset($query['contain']) || (isset($query['contain']) && (in_array('Draft', $query['contain']) || key_exists('Draft', $query['contain'])))) {
				$bind_options = array(
					'className' => 'Draft.Draft',
					'foreignKey' => 'model_id',
					'conditions' => array("Draft.model" => $Model->alias)
				);
				if (isset($query['contain']['Draft'])) {
					$bind_options = array_merge($bind_options, $query['contain']['Draft']);
				}
				$Model->bindModel(array(
					'hasMany' => array(
						'Draft' => $bind_options
					)
				));
			}
		}
		return $Model->beforeFind($query);
	}

	/**
	* Bind the icing model on demand, this is useful right before a call in which you want to contain
	* but don't have the association.
	*/
	public function bindDraft(Model $Model){
		$bind_options = array(
			'className' => 'Draft.Draft',
			'foreignKey' => 'model_id',
			'conditions' => array("Draft.model" => $Model->alias)
		);
		$Model->bindModel(array(
			'hasMany' => array(
				'Draft' => $bind_options
			)
		));
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
		if (isset($options['contain'])) {
			$options['contain'][] = 'Draft'; //Add draft to it if we don't have it.
		}
		$options = array_merge(array(
			'draft' => true,
			'contain' => array('Draft')
		), (array) $options);
		$data = $Model->find('first', $options);
		
		//Copy original record into it's ModelOriginal key.
		$data[$Model->alias . 'Original'] = $data[$Model->alias];
		if (!empty($data) && !empty($data['Draft'])) {
			//Grab and merge the draft data into the alias.
			$draft_data = json_decode($data['Draft']['json'], true);
			$data[$Model->alias] = array_merge($data[$Model->alias], $draft_data[$Model->alias]);
		}
		return $data;
	}
	
	/**
	* Find a draft by the logged in user.
	* @param Model model
	* @param array of options
	* @return non-saved data of drafts
	*/
	public function findDraftByUser(Model $Model, $options = array()) {
		$draft = $this->Draft->find('first', array(
			'conditions' => array(
				'user_id' => $this->userId(),
				'model' => $Model->alias
			),
			'order' => array('Draft.modified DESC')
		));
		$retval = array();
		if (!empty($draft)) {
			$retval[$Model->alias] = json_decode($data['Draft']['json'], true);
		}
		return $retval;
	}

	/**
	* Get the version data from the Model based on settings and deleting
	* this is used in beforeDelete and beforeSave
	* @param Model model
	* @param boolean deleted
	*/
	private function saveDraft(Model $Model){
		$model_id = null;
		if ($Model->id) {
			$model_id = $Model->id;
		}
		if (isset($Model->data[$Model->alias][$Model->primaryKey]) && !empty($Model->data[$Model->alias][$Model->primaryKey])) {
			$model_id = $Model->data[$Model->alias][$Model->primaryKey];
		}
		$data = $Model->data; //cache the data incase the model has some afterfind stuff that sets data
		$draft_data = array(
			'user_id' => $this->userId($Model),
			'model_id' => $model_id,
			'model' => $Model->alias,
			'json' => json_encode($data)
		);
		return $this->Draft->saveVersion($draft_data, $this->settings[$Model->alias]);
	}
	
	/**
	* Grab the userID from the AuthComponent or getUserId in the model somewhere.
	* @param Model model
	* @return mixed int of ID or null
	*/
	private function userId(Model $Model) {
		if (method_exists($Model, 'getUserId')) {
			return $Model->getUserId();
		} elseif (class_exists('AuthComponent') && class_exists('CakeSession') && CakeSession::started()) {
			return AuthComponent::user('id');
		}
		return null;
	}
	
	/**
	* Delete all the draft data saved for a paticular record
	* @param Model model
	* @return boolean success
	*/
	private function deleteDraft(Model $Model) {
		$conditions = array(
			'model' => $Model->alias,
			'model_id' => $Model->id,
		);
		if ($this->Draft->hasAny($conditions)) {
			return $this->Draft->deleteAll($conditions);
		}
		return true;
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
