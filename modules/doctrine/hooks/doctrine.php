<?php
ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.APPPATH.'models/doctrine'.PATH_SEPARATOR.APPPATH.'models/doctrine/generated');

require_once(dirname(__FILE__).'/../vendor/Doctrine.php');

$db = Kohana::config('database.default');

$conn_string = "{$db['connection']['type']}://{$db['connection']['user']}:{$db['connection']['pass']}@{$db['connection']['host']}/{$db['connection']['database']}";
// Set the autoloader
spl_autoload_register(array('Doctrine', 'autoload'));


$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);

// Load the Doctrine connection
$conn = Doctrine_Manager::connection($conn_string);

// Load the models for the autoloader
Doctrine::loadModels(APPPATH . DIRECTORY_SEPARATOR . 'models/doctrine');

########### Profiling
global $doctrine_profiler;
$doctrine_profiler = new Doctrine_Connection_Profiler();

$conn->setListener($doctrine_profiler);

Event::add('profiler.run', 'doctrine_render_queries');

function doctrine_render_queries()
{
  global $doctrine_profiler; // Surely a neater way to do this!
  
  $obj = Event::$data;
  $table = $obj->table('doctrine');
  $table->add_column();
	$table->add_column('kp-column kp-data');
	$table->add_column('kp-column kp-data');
	$table->add_row(array('Doctrine Queries', 'Action', 'Time'), 'kp-title', 'background-color: #E0FFE0');
	
	$time = 0;
  foreach ($doctrine_profiler as $event) {
    $time += $event->getElapsedSecs();
		if (in_array($event->getName(), array('query', 'execute'))) {
			$data = array(
			              $event->getQuery().' - ('.implode(', ', $event->getParams()).')',
			              $event->getName(),
			              sprintf("%f", $event->getElapsedSecs()));
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}
  }
  
  $data = array('Total: ', number_format($time, 8));
	$table->add_row($data, 'kp-totalrow');
}

#new Profiler;