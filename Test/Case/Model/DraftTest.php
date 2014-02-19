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
	}

}
