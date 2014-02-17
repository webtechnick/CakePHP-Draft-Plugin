<?php
App::uses('DraftAppModel', 'Draft.Model');
/**
 * Draft Model
 *
 */
class Draft extends DraftAppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'model';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'model' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				'message' => 'Must be associated with a model.',
			),
		),
		'json' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				'message' => 'Model data must be present.',
			),
		),
	);

	/**
	* List of fields to search for using quick search
	*/
	public $searchFields = array('Draft.id','Draft.model');

	/**
	* List of fields that can be updated via ajax.
	*/
	public $ajaxFields = array();
	
	public function saveDraft($data, $settings = array()) {
		$conditions = array(
			'model' => $data['model'],
			'model_id' => $data['model_id'],
		);
		//check if we have this already, if so, overwrite it.
		if ($this->hasAny($conditions)) {
			$data['id'] = $this->field('id', $conditions);
		}
		$this->create();
		return $this->save($data);
	}
}
