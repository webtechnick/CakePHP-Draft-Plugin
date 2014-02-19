<?php
App::uses('Draft', 'Draft.Model');

/**
 * Draft Test Case
 *
 */
class DraftTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.draft.draft'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Draft = ClassRegistry::init('Draft.Draft');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Draft);

		parent::tearDown();
	}

/**
 * testSaveDraft method
 *
 * @return void
 */
	public function testSaveDraft() {
		$data = array(
			'model' => 'TestModel',
			'model_id' => 1,
			'json' => '{"TestModel":{"id":1,"body":"This is a subtle change.","modified":"2014-02-18 20:58:45"}}'
		);
		$result = $this->Draft->saveDraft($data);
		$this->assertTrue(!empty($result));
	}

}
