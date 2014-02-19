<?php
/**
 * DraftFixture
 *
 */
class DraftFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'model_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'model' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 26, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'key' => 'index'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'key' => 'index'),
		'json' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'model_id' => array('column' => 'model_id', 'unique' => 0),
			'model' => array('column' => 'model', 'unique' => 0),
			'user_id' => array('column' => 'user_id', 'unique' => 0),
			'created' => array('column' => 'created', 'unique' => 0),
			'modified' => array('column' => 'modified', 'unique' => 0),
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '52fbbeea-3808-4492-b33c-4335e017215a',
			'model_id' => '1',
			'model' => 'TestModel',
			'user_id' => null,
			'created' => '2014-02-10 11:35:22',
			'modified' => '2014-02-10 11:35:22',
			'json' => '{"TestModel":{"id":1,"body":"This is a subtle change.","modified":"2014-02-18 20:58:45"}}'
		),
		array(
			'id' => '52fbbeea-3808-4492-b33c-4335e017215b',
			'model_id' => null,
			'model' => 'TestModel',
			'user_id' => 1,
			'created' => '2014-02-12 11:35:22',
			'modified' => '2014-02-12 11:35:22',
			'json' => '{"TestModel":{"id":null,"body":"This is a new record but unsaved.","modified":"2014-02-18 20:58:45"}}'
		),
	);

}
