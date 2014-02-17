<?php
App::uses('Draft', 'Draft.Model');
App::uses('BaseTest', 'Test/Case');

/**
 * Draft Test Case
 *
 */
class DraftTest extends BaseTest {

/**
 * Additional Fixtures
 *
 * @var array
 */
	public $addFixtures = array(
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

}
