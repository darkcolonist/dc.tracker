<?php defined('SYSPATH') OR die('No direct access allowed.');
class Day_Task_Controller extends Protected_Template_Controller {

  function  __construct() {
    parent::__construct();

    $this->auto_render = false;
  }

  public function index() {
    $task = new Task();
    $search_params = $this->input->get();
    $task->process($this->input->post("txtNewTask"));
    $model = new TblTaskLines();
    $today = date("Y-m-d");
    $days = $task->build_days($model->get_by_date($today));

    if(isset($days[$today]) == false)
      die("nothing to show.");

    $day = $days[$today];
    
    echo View::factory("day_content", array(
        "day_name" => $today,
        "day" => $day
    ));
  }

  public function date($date){
    $task = new Task();
    $search_params = $this->input->get();
    $task->process($this->input->post("txtNewTask"));
    $model = new TblTaskLines();
    $days = $task->build_days($model->get_by_date($date));

    if(isset($days[$date]) == false)
      die("nothing to show.");

    $day = $days[$date];

    echo View::factory("day_content", array(
        "day_name" => $date,
        "day" => $day
    ));
  }

  public function dates($dates_string){
    $task = new Task();
    $model = new TblTaskLines();

    $dates = explode(",", $dates_string);

    $toecho = array();

    foreach($dates as $key => $date){

      $days = $task->build_days($model->get_by_date($date));

      $toecho[$key]["container"] = $date;

      if(isset($days[$date])){
        $day = $days[$date];

        $toecho[$key]["html"] = View::factory("day_content", array(
            "day_name" => $date,
            "day" => $day
        ))->render(false);
      }else{
        $toecho[$key]["html"] = "nothing to show.";
      }

    }

    echo json_encode($toecho);
  }

  //<editor-fold desc="suggest helpers" collapsed="collapsed">

  protected function suggest_build_info($val, $type){
    return "<span class='suggest-val'>{$val}</span> <span class='suggest-info'>{$type}</span>";
  }

  protected function suggest_db($keyword, $results_array){
    $model = new TblTaskLines();
    $result = $model->suggest_query($keyword);

    if(count($result) > 0){
      foreach($result as $row){
        $row["value"] = str_replace(" ", "_", $row["value"]);

        $results_array[] = array(
            "value" => $row["value"],
            "display" => $this->suggest_build_info($row["value"], $row["type"])
        );
      }
    }

    return $results_array;
  }

  //</editor-fold>

  public function suggest(){
    $keyword = $this->input->post("value");

    if(trim($keyword) == null) return;

    $results = array();

    $results = $this->suggest_db($keyword, $results);

    echo json_encode($results);
  }

}