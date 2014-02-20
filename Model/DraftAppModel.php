<?php

App::uses('AppModel', 'Model');

class DraftAppModel extends AppModel {
	/**
	 * Always use Containable
	 *
	 * var array
	 */
	public $actsAs = array('Containable');

	/**
	 * Always set recursive = 0
	 * (we'd rather use containable for more control)
	 *
	 * var int
	 */
	public $recursive = 0;
	/**
	 * Filter fields
	 *
	 * @var array
	 */
	public $searchFields = array();

	/**
	 * return conditions based on searchable fields and filter
	 *
	 * @param string filter
	 * @return conditions array
	 */
	public function generateFilterConditions($filter = NULL, $pre = '%') {
		$retval = array();
		if ($filter) {
			foreach ($this->searchFields as $field) {
				$retval['OR']["$field LIKE"] =  $pre . $filter . '%';
			}
		}
		return $retval;
	}
}
