<?php
App::uses('DraftsController', 'Draft.Controller');
App::uses('BaseTest', 'Test/Case');

/**
 * DraftsController Test Case
 *
 */
class DraftsControllerTest extends BaseTest {

/**
 * Additional Fixtures
 *
 * @var array
 */
	public $addFixtures = array(
		'plugin.draft.draft',
		'plugin.draft.location',
		'plugin.draft.user',
		'plugin.draft.corp',
		'plugin.draft.content',
		'plugin.draft.tag',
		'plugin.draft.content_tag',
		'plugin.draft.product',
		'plugin.draft.products_content_join',
		'plugin.draft.call_source',
		'plugin.draft.hour',
		'plugin.draft.staff',
		'plugin.draft.review',
		'plugin.draft.zip',
		'plugin.draft.survey_caller',
		'plugin.draft.survey_call',
		'plugin.draft.survey_admin_note',
		'plugin.draft.note',
		'plugin.draft.import_status',
		'plugin.draft.location_user'
	);

}
