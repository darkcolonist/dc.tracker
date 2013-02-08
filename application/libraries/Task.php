<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tools needed to create a task. Display, formulation...
 *
 * @package    Task
 * @author     darkcolonist <darkcolonist@gmail.com>
 * @copyright  (c) 2011 darkcolonist
 */
class Task {

  public $commands = array(
      "project" => "-p ",
      "task" => "-t ",
      "date" => "-d ",
      "status" => "-s ",
      "pinned" => "-pin ",
      "issue" => "#",
      "show" => "--show ",
      "search" => "--search ",
      "mode" => "-m ",
      "due" => "-dd ",
  );
  protected $core_commands = array(
      "show" => "--show ",
      "search" => "--search ",
  );
  public $commands_help = array(
      "task" => "task id",
      "date" => "yyyy-mm-dd [default=now]",
      "status" => "done, crit, high, pend [default=done]",
      "pinned" => "display task on today until it gets unpinned [0,1]",
      "show" => "status",
      "search" => "keyword",
      "mode" => "queue, task [default=task]",
      "due" => "mode=queue only, yyyy-mm-dd",
  );

  /**
   * 
   * step repeater for days
   *
   * this will duplicate tasks per day until the date of completion or today.
   *
   * @param array $days_array
   * @param TblTaskLines $day_item
   * @return array
   */
  protected function build_days_step(array $days_array, TblTaskLines $day_item){
    $start_date = date("Y-m-d",strtotime($day_item->date_created));
    $end_date = null;
    $special_tag = null;

    if($day_item->date_completed == null){
      // this is an unfinished task, iterate until today
      $end_date = date("Y-m-d");
      $special_tag = "ongoing";
    }else{
      // this is a completed task, iterate until date of completion
      $end_date = date("Y-m-d",strtotime($day_item->date_completed));
      $special_tag = "completed";
    }

    $dates = util::get_dates_array($start_date, $end_date, true);

    foreach($dates as $the_date){
      $days_array = $this->build_days_to_array($days_array, $day_item, $the_date, $special_tag);
    }

    return $days_array;
  }

  /**
   *
   * transmigrate doctrine_record entity to array
   *
   * @param array $days_array
   * @param TblTaskLines $day_item
   * @param string $custom_date
   * @return array
   */
  protected function build_days_to_array(
    $days_array,
    TblTaskLines $day_item,
    $custom_date = null,
    $special_tag = null){

    if($custom_date == null){
      $today = date("Y-m-d");
      $raw_day = $day_item->day;

      if($day_item->is_pinned)
        $raw_day = $today . " " . date("h:i a", strtotime($raw_day));
    }else{
      $today = $custom_date;
      $raw_day = $day_item->day;
      $raw_day = $today . " " . date("h:i a", strtotime($raw_day));
    }

    $adjusted_day = date("Y-m-d", strtotime($raw_day));
    $adjusted_time = date("h:i a", strtotime($raw_day));
    $adjusted_time = ltrim($adjusted_time, "0");
    $adjusted_time_string = $adjusted_time;

    if($day_item->status == "done")
      $adjusted_time_string .= " | time spent: " .
        util::time_quanta(
            strtotime($day_item->date_completed) -
            strtotime($day_item->date_created)
        );

    if($day_item->is_pinned)
      $adjusted_time_string .= " | pinned | origin: " .
      util::get_time_proximity(date("Y-m-d", strtotime($day_item->day)));

    if(date("Y-m-d",strtotime($day_item->date_created)) != $adjusted_day &&
            !$day_item->is_pinned)
      $adjusted_time_string .= " | carry-over | origin: " .
      util::get_time_proximity(date("Y-m-d", strtotime($day_item->day)));

    //<editor-fold desc="region" collapsed="array allocator">
    $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["task"] = $this->parse_redmine($day_item->description);
    $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["timestamp"] = $adjusted_time;
    $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["timestamp_string"] = $adjusted_time_string;
    $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["command"] = $this->build_command($day_item);
    $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["command_duplicate"] = $this->build_duplicate_command($day_item);
    $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["command_delete"] = $this->build_delete_command($day_item);
    $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["status"] = $day_item->status;
    $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["is_pinned"] = $day_item->is_pinned;
    $days_array[$adjusted_day][$day_item->group_name]["command"] =
      $this->commands["project"] . str_replace(" ", "_", $day_item->group_name) . " ";
    
    if($special_tag != null)
      $days_array[$adjusted_day][$day_item->group_name]["lines"][$day_item->hash_key]["special"] = $special_tag;

    //</editor-fold>

    return $days_array;
  }

  protected function sort($days){
    // sort by day
    krsort($days);

    foreach($days as $key => $day){
      // sort by group
      ksort($days[$key]);
    }
    return $days;
  }

  /**
   *
   * only return days in scope (see application.day_limit config)
   *
   * @param array $days_array
   * @return array
   */
  protected function build_days_limit($days_array){

    foreach($days_array as $key => $day){
      $curdate = date("Y-m-d");

      if(util::date_is_over_limit($key, $curdate))
        unset($days_array[$key]);
    }

    return $days_array;
  }

  public function build_days(Doctrine_Collection $days_collection) {
    $days = array();

    if ($days_collection->count() > 0) {
      foreach ($days_collection as $day) {
        
        // transmigrate entity to array
        $days = $this->build_days_to_array($days, $day);

        // repeat days
        $days = $this->build_days_step($days, $day);

        // only return days in scope (see application.day_limit config)
        $days = $this->build_days_limit($days);
      }
    }

    // sort
    $days = $this->sort($days);
    
    return $days;
  }

  protected function clean_command($dirty_command) {
    $clean_command = $dirty_command;
    $clean_command = htmlentities($clean_command);
    $clean_command = trim($clean_command);

    return $clean_command;
  }

  protected function build_command(TblTaskLines $day) {
    $command = null;

    $command .= $day->group_name == null ? null : "{$this->commands["project"]}" . str_replace(" ", "_", $day->group_name);
    $command .= " {$this->commands["task"]}{$day->hash_key}";
    $command .= " {$day->description}";

    return $this->clean_command($command);
  }

  protected function build_duplicate_command(TblTaskLines $day) {
    $command = null;

    $command .= $day->group_name == null ? null : "{$this->commands["project"]}" . str_replace(" ", "_", $day->group_name);
    $command .= " {$this->commands["status"]}{$day->status}";
    $command .= " {$day->description}";

    return $this->clean_command($command);
  }

  protected function build_delete_command(TblTaskLines $day) {
    $command = null;

    $command .= "{$this->commands["task"]}{$day->hash_key}";

    return $this->clean_command($command);
  }

  protected function core_commands($text) {
    foreach($this->core_commands as $key => $command){
      $var = $this->parse_command($text, $command);

      if ($var != null && $var != "all")
        die(json_encode(array("url" => url::base()."?{$key}={$var}")));
      else if ($var == "all")
        die(json_encode(array("url" => url::base())));
    }
  }

  public function process($task_input) {

    if (trim($task_input) == false) {
      return false;
    }

    // attempt to execute core commands first.
    $this->core_commands($task_input);

    $toggle_pinned = false;

    $this->auto_render = false;

    $data = $this->parse_text($task_input);
    
    $model = new TblTaskLines();

    if ($data["task_id"] != null)
      $model = $model->get_one_by_hash($data["task_id"]);

    if ($model == null || $model->id == null) {
      $model = new TblTaskLines();
      $model->hash_key = strtolower(text::random("alnum", 5));
    }

    if ($data["task"] == null && $data["task_id"] != null) {

      $record = Doctrine::getTable("TblTaskLines")->findOneByhash_key($data["task_id"]);
      $record->delete();
    } else {

      if ($data["date"] != null)
        $model->date_created = $data["date"] . " " . date("H:i");

      if ($data["pinned"] !== null){
        $toggle_pinned = true;
        $model->is_pinned = $data["pinned"];
      }

      if ($data["mode"] == "queue"){
        $model->type = "queue";
      }

      $model->date_due = $data["due"];
      $model->status = $data["status"];
      $model->raw_command = $data["raw"];
      $model->group_name = $data["group"];
      $model->description = $data["task"];
      $model->save();
    }

    if($model->date_modified != null)
      $to_echo["dates"] =
          date("Y-m-d", strtotime($model->date_modified))
          .",".date("Y-m-d", strtotime($model->date_created));
    else
      $to_echo["dates"] = date("Y-m-d", strtotime($model->date_created));

    $to_echo["data"] = $data;
    $to_echo["message"] = $model->hash_key." has been saved.";
    echo json_encode($to_echo);
    die();
  }

  protected function parse_text($dyna_text) {
    $parsed = array();

    $parsed["raw"] = trim($dyna_text);
    $parsed["group"] = $this->parse_command($dyna_text, $this->commands["project"]);
    $parsed["task_id"] = $this->parse_command($dyna_text, $this->commands["task"]);
    $parsed["date"] = $this->parse_command($dyna_text, $this->commands["date"]);
    $parsed["status"] = $this->parse_command($dyna_text, $this->commands["status"]);
    $parsed["pinned"] = $this->parse_command($dyna_text, $this->commands["pinned"]);
    $parsed["mode"] = $this->parse_command($dyna_text, $this->commands["mode"]);
    $parsed["due"] = $this->parse_command($dyna_text, $this->commands["due"]);
    $parsed["task"] = trim($dyna_text);

    return $parsed;
  }

  protected function parse_command(&$text, $command) {
    $parsed_command = null;

    $group_pattern = "/{$command}[a-zA-Z0-9_-]+/";

    preg_match($group_pattern, $text, $group_matches);

    if (is_array($group_matches) && isset($group_matches[0]) && trim($group_matches[0]) != null) {

      $parsed_command = str_replace($command, "", $group_matches[0]);
      $parsed_command = str_replace("_", " ", $parsed_command);
      $parsed_command = strtolower($parsed_command);

      $text = str_replace($group_matches[0], "", $text);
    }

    return $parsed_command;
  }

  protected function parse_redmine($text) {
    $parsed_command = null;

    $text = $this->clean_command($text);

    $command = $this->commands["issue"];

    $group_pattern = "/{$command}[0-9]+/";

    preg_match_all($group_pattern, $text, $group_matches);


    if (is_array($group_matches[0])) {
      $url_base = Kohana::config("application.redmine_url");
      foreach ($group_matches[0] as $match) {
        $url = $url_base . str_replace($command, "", $match);
        $text = str_replace($match, html::anchor($url, $match, array("rel" => "external")), $text);
      }
    }

    return $text;
  }

}

// End Task
