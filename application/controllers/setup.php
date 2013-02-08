<?php defined('SYSPATH') OR die('No direct access allowed.');
class Setup_Controller extends Protected_Template_Controller {

  function  __construct() {
    parent::__construct();

    $this->auto_render = false;
  }

  public function index() {
    echo "setup appears to be working!";
  }

  public function doctrine() {
    echo "doctrine setup initated<br/>";

    Doctrine::generateModelsFromDb(
                    'application/models/doctrine',
                    array(),
                    array()
    );

    echo "doctrine setup completed<br/>";
  }

  public function suggest(){

    $con = Doctrine_Manager::connection();

    $primary_query = "delete from tbl_suggestions;\n"; // empty the table first

    $dates = null;
    //<editor-fold desc="dates" collapsed="collapsed">
    $date_range = kohana::config("application.suggest_date_range");
    $days_count = $date_range * 2;
    $start_date = date("Y-m-d", strtotime("-{$date_range}DAYS"));

    for( $i = 0 ; $i < $days_count ; $i ++ ){

      $ts = strtotime($start_date . " +{$i}DAYS");
      $new_date = date("Y-m-d", $ts);
      $type = date("l F d, Y", $ts) . " | date";
      
      $dates .= "insert into tbl_suggestions(`value`,`type`) values('{$new_date}','{$type}');\n";

    }
    //</editor-fold>

    $primary_query .= $dates; // add dates list
    
    //<editor-fold desc="status" collapsed="collapsed">
    $primary_query .= "insert into tbl_suggestions(`value`,`type`) values('crit','status');\n";
    $primary_query .= "insert into tbl_suggestions(`value`,`type`) values('high','status');\n";
    $primary_query .= "insert into tbl_suggestions(`value`,`type`) values('pend','status');\n";
    $primary_query .= "insert into tbl_suggestions(`value`,`type`) values('done','status');\n";
    //</editor-fold>

    $con->execute($primary_query);

    echo "done.";

  }

}