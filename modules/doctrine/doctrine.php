<?php
define('SYSPATH', true);
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../application/config/database.php');

// Configure Doctrine Cli
// Normally these are arguments to the cli tasks but if they are set here the arguments will be auto-filled
$doctrine_config = array('data_fixtures_path'  =>  dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../application/config/doctrine/fixtures',
                'models_path'         =>  dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../application/models/doctrine',
                'migrations_path'     =>  dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../application/config/doctrine/migrations',
                'sql_path'            =>  dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../application/config/doctrine/sql',
                'yaml_schema_path'    =>  dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../application/config/doctrine/schema');

                
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor/Doctrine.php');        

// Set the autoloader
spl_autoload_register(array('Doctrine', 'autoload'));
$db =& $config['default'];
$conn = "{$db['connection']['type']}://{$db['connection']['user']}:{$db['connection']['pass']}@{$db['connection']['host']}/{$db['connection']['database']}";

// Load the Doctrine connection
Doctrine_Manager::connection($conn);

$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);
// Load the models for the autoloader
#Doctrine::loadModels($doctrine_config['models_path']);

// And instantiate
$cli = new Doctrine_Cli($doctrine_config);
$cli->run($_SERVER['argv']); 