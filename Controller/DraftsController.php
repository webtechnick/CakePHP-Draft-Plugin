<?php
App::uses('DraftAppController', 'Draft.Controller');
/**
 * Drafts Controller
 */
class DraftsController extends DraftAppController {
	
	public $uses = array();
	
	/**
	* Allow save if Auth is around.
	*/
	public function beforeFilter() {
		parent::beforeFilter();
		if (isset($this->Auth)) {
			$this->Auth->allow('save','delete');
		}
	}

	/**
	* Save a draft, executed via ajax using the DraftHelper
	* @param string model
	* @return boolean success
	*/
	public function save($model = null) {
		$this->autoRender = false;
		if (!empty($this->request->data) && $model) {
			$Model = ClassRegistry::init($model);
			if ($Model && $Model->Behaviors->attached('Draftable')) {
				return !!$Model->saveDraft($this->request->data);
			}
		}
		return false;
	}
	
	/**
	* Discard the draft, executed via ajax using DraftHelper
	* @param string model
	* @return boolean success
	*/
	public function delete($model = null, $model_id = null) {
		$this->autoRender = false;
		if ($model) {
			$Model = ClassRegistry::init($model);
			if ($Model && $Model->Behaviors->attached('Draftable')) {
				$Model->deleteDraft($model_id);
			}
		}
		return $this->redirect($_SERVER['HTTP_REFERER']);
	}
}
