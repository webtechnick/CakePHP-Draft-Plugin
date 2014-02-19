<?php
App::uses('DraftableBehavior', 'Draft.Model/Behavior');
App::uses('DraftAppModel', 'Draft.Model');
/**
* TestModel used to be Draftable.
*/
class TestModel extends DraftAppModel {
	public $useDbConfig = 'test';
	public $actsAs = array('Draft.Draftable');
	public function getUserId($user = null) {
		return 9999; //doens't exist
	}
}
/**
 * DraftableBehavior Test Case
 *
 */
class DraftableBehaviorTest extends CakeTestCase {
	
	public $fixtures = array(
		'plugin.draft.draft',
		'plugin.draft.test_model'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->TestModel = ClassRegistry::init('TestModel');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TestModel);
		parent::tearDown();
	}
	
	public function test_beforeSave() {
		$original = $this->TestModel->find('first');
		$data = array(
			'TestModel' => array(
				'id' => 1,
				'body' => 'This is a subtle change.'
			)
		);
		$result = $this->TestModel->save($data, array('draft' => true));
		$this->assertFalse($result); //didn't save, but we have a draft instead.
		
		//Assert no change was made
		$result = $this->TestModel->find('first', array('conditions' => array('TestModel.id' => 1)));
		$this->assertNotEqual($data['TestModel']['body'],$result['TestModel']['body']);
		//Assert Draft wasn't autobound.
		$this->assertTrue(empty($result['Draft']));
		
		//Grab it again and assert it's there.
		
		//See the Draft
		$this->TestModel->bindDraft();
		$result2 = $this->TestModel->findDraft(array(
			'conditions' => array('TestModel.id' => 1)
		));
		//Assert the change is there.
		$this->assertEqual($data['TestModel']['body'],$result2['TestModel']['body']);
		//Assert the original is there too.
		$this->assertEqual($result2['TestModelOriginal']['body'],$original['TestModel']['body']);
	}
	
	public function test_beforeFind() {
		$result = $this->TestModel->find('first');
		$this->assertTrue(empty($result['Draft'])); //we don't have a draft.
		
		$result = $this->TestModel->find('first', array('draft' => true));
		$this->assertFalse(empty($result['Draft'])); //we have a draft data as contained.
		
		$result = $this->TestModel->find('first', array('contain' => array('Draft')));
		$this->assertFalse(empty($result['Draft'])); //we have draft data, we explicicty asked for it.
		
		$this->TestModel->Behaviors->load('Draftable', array('bind' => true));
		$result = $this->TestModel->find('first');
		$this->assertFalse(empty($result['Draft'])); //we have draft data, we autubind it.
		
		$this->TestModel->Behaviors->load('Draftable', array('bind' => true));
		$result = $this->TestModel->find('first', array('contain' => array()));
		$this->assertTrue(empty($result['Draft'])); //we have no draft data, we autobind, but request no contains.
	}
	
	public function test_mergeDraft() {
		$result = $this->TestModel->find('first', array('draft' => true));
		$this->assertFalse(empty($result['Draft'])); //we have draft data, we explicicty asked for it.
		
		$data = $this->TestModel->mergeDraft($result);
		$this->assertFalse(empty($data['TestModelOriginal']));
		$this->assertEqual('This is a subtle change.', $data['TestModel']['body']); 
	}
	
	public function test_findDraft() {
		$data = $this->TestModel->findDraft(array(
			'conditions' => array('TestModel.id' => 1)
		));
		$this->assertFalse(empty($data['TestModelOriginal']));
		$this->assertEqual('This is a subtle change.', $data['TestModel']['body']);
	}

	public function test_findDraftByUser() {
		$data = $this->TestModel->findDraftByUser();
		$this->assertTrue(empty($data));
		
		$data = $this->TestModel->findDraftByUser(1); //User ID of 1
		$this->assertFalse(empty($data));
		$this->assertEqual('This is a new record but unsaved.', $data['TestModel']['body']);
	}
	
	public function test_cleanupDrafts() {
		$Draft = ClassRegistry::init('Draft.Draft');
		$count = $Draft->find('count');
		
		$result = $this->TestModel->cleanupDrafts();
		$this->assertEqual($result, 1);
		
		$this->assertEqual($count - 1, $Draft->find('count'));
	}
	
	public function test_saveDraft() {
		$Draft = ClassRegistry::init('Draft.Draft');
		$count = $Draft->find('count');
		
		$this->TestModel->data = array(
			'TestModel' => array(
				'id' => 1,
				'body' => 'This is a change.'
			)
		);
		$result = $this->TestModel->saveDraft();
		$this->assertTrue(!empty($result));
		$this->assertEqual(9999, $result['Draft']['user_id']);
		$this->assertEqual(1, $result['Draft']['model_id']);
		$this->assertEqual('TestModel', $result['Draft']['model']);
		
		//Assert we overwrote the previous draft, not a new one.
		$this->assertEqual($count, $Draft->find('count'));
		
		//Assert we have the same save if we pass in data.
		$result = $this->TestModel->saveDraft($this->TestModel->data);
		$this->assertTrue(!empty($result));
		$this->assertEqual(9999, $result['Draft']['user_id']);
		$this->assertEqual(1, $result['Draft']['model_id']);
		$this->assertEqual('TestModel', $result['Draft']['model']);
		
		//Assert we overwrote the previous draft, not a new one.
		$this->assertEqual($count, $Draft->find('count'));
	}
	
	public function test_deleteDraft() {
		$Draft = ClassRegistry::init('Draft.Draft');
		$count = $Draft->find('count');
		
		$result = $this->TestModel->delete(1);
		$this->assertTrue($result);
		
		//Assert we also deleted the draft
		$this->assertEqual($count - 1, $Draft->find('count'));
	}
	
	public function test_afterSave() {
		$Draft = ClassRegistry::init('Draft.Draft');
		$count = $Draft->find('count');
		
		$this->TestModel->id = 1;
		$this->TestModel->read();
		$result = $this->TestModel->save();
		$this->assertTrue(!empty($result));
		
		//Assert we also deleted the draft with a successful save
		$this->assertEqual($count - 1, $Draft->find('count'));
	}
}
