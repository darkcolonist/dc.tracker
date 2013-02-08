<?php

defined('SYSPATH') OR die('No direct access allowed.');

class Test_Controller extends Controller {

  public function index(){
    $con = Doctrine_Manager::connection();
    $result = $con->fetchAssoc("SELECT NOW() as the_date;");

    $mysql_time = $result[0]["the_date"];
    $php_time = Date("Y-m-d H:i:s");

    $difference = date("H", strtotime($mysql_time) - strtotime($php_time));
    $difference = $difference;

    echo "mysql time: ". $mysql_time . "<hr/>";
    echo "php time: ". $php_time . "<hr/>";
    echo "the difference: ". $difference ." more hours is needed for php to match mysql time";
  }

  public function dates($start, $end){
    $result = util::get_dates_array($start, $end);

    echo kohana::debug($result);
  }

  public function args_builder(){
    $args = array(
      'ls',
      '-x',
      '123123',
      '-p',
      'head',
      'programmer',
      'herpderp',
      '-m',
      '-raw'
    );
    
    $formatted_args = util::terminal_build_command_array($args);
    
    echo '<pre>'.print_r($formatted_args, true);
  }
}