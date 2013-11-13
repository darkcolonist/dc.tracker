<?php defined('SYSPATH') OR die('No direct access allowed.');
$config['default'] = array
(
	'benchmark'     => TRUE,
	'persistent'    => FALSE,
	'connection'    => array
	(
		'database' => 'dc_tracker',
		'host'     => 'localhost',
		'user'     => 'root',
		'pass'     => 'qwerty321',
		'port'     => FALSE,
		'socket'   => FALSE,
		'type'     => 'mysql'
	),
	'character_set' => 'utf8',
	'table_prefix'  => '',
	'object'        => TRUE,
	'cache'         => FALSE,
	'escape'        => TRUE
);

$config['online_misty'] = array
(
	'benchmark'     => TRUE,
	'persistent'    => FALSE,
	'connection'    => array
	(
		'database' => 'xtian10_tracker',
		'host'     => 'localhost',
		'user'     => 'xtian10_tracker',
		'pass'     => '3DsQBFPfxcO4',
		'port'     => FALSE,
		'socket'   => FALSE,
		'type'     => 'mysql'
	),
	'character_set' => 'utf8',
	'table_prefix'  => '',
	'object'        => TRUE,
	'cache'         => FALSE,
	'escape'        => TRUE
);