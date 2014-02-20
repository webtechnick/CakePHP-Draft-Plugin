<?php
App::uses('DraftAppController', 'Draft.Controller');
/**
 * Drafts Controller
 */
class DraftsController extends DraftAppController {
	
	public $paginate = array(
		'order' => 'Draft.modified DESC'
	);
	
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
	
	/**
	* Admin index of drafts.
	*/
	public function admin_index($filter = null) {
		if (!empty($this->request->data)) {
			$filter = $this->request->data['Draft']['filter'];
		}
		$conditions = $this->Draft->generateFilterConditions($filter);
		$this->set('drafts',$this->paginate($conditions));
		$this->set('filter', $filter);
	}
	
	public function admin_edit() {
		$this->Session->setFlash('Add/Modify Drafts using the Helper on your edit forms.');
		return $this->redirect(array('action' => 'index'));
	}
	
	/**
	* Admin view of drafts
	*/
	public function admin_view($id) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid Draft ID'));
			return $this->redirect(array('action' => 'index'));
		}
		$this->set('draft', $this->Draft->read(null, $id));
		$this->set('id', $id);
	}
}
