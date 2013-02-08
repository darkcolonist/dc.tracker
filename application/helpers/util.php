<?php defined('SYSPATH') OR die('No direct access allowed.');
class Util {

  static function test($string) {
    return "you specified {$string}";
  }

  static function get_time_proximity($day_name) {
    $proximity_string = null;

    if ($day_name < date("Y-m-d")) {
      $age = (strtotime(date("Y-m-d")) - strtotime($day_name)) / 60 / 60 / 24;
      if ($age == 1) {
        $proximity_string = "yesterday";
      } else {
        $proximity_string = $age . " day" . ($age > 1 ? "s" : null) . " ago";
      }
    } elseif ($day_name > date("Y-m-d")) {
      $age = (strtotime($day_name) - strtotime(date("Y-m-d"))) / 60 / 60 / 24;
      if ($age == 1) {
        $proximity_string = "tomorrow";
      } else {
        $proximity_string = $age . " day" . ($age > 1 ? "s" : null) . " from now";
      }
    } else {
      $proximity_string = "today";
    }

    return $proximity_string;
  }

  static function time_quanta($time) {
    $d = intval(($time / 86400));
    $h = intval(($time / 3600) % 24);
    $m = intval(($time / 60) % 60);
    $s = intval($time % 60);

    if (!max($d, $h, $m, $s))
      return "0s";
    
    $st = '';
    if ($d > 0)
      $st .= ( $d < 10 ? '0' . $d : $d) . 'd ';
    
    if ($h > 0 || $d > 0)
      $st .= ( $h < 10 ? '0' . $h : $h) . 'h ';

    $st .= ( $m < 10 ? '0' . $m : $m) . 'm ';
    $st .= ( $s < 10 ? '0' . $s : $s) . 's';

    return $st;
  }

  static function get_dates_array($start, $end, $exclude_weekends = false){
    $start_time = strtotime($start);
    $end_time = strtotime($end);

    $days_difference = $end_time - $start_time;
    $days_difference = $days_difference / 60 / 60 /24;

    $dates_array = array();

    for($i = $days_difference ; $i > 0 ; $i --){
      $the_date = date("Y-m-d", strtotime($start . "+{$i}DAY"));

      if($exclude_weekends){
        $day_num = date("N", strtotime($the_date));

        if($day_num != 6 && $day_num != 7)
          $dates_array[] = $the_date;
      }else{
        $dates_array[] = $the_date;
      }
    }

    return $dates_array;
  }

  /**
   *
   * will check if the date is over limit prior to the current date.
   *
   * @param string $date the date to be tested
   * @param string $curdate the date prior to
   */
  static function date_is_over_limit($date, $curdate){
    $limit = Kohana::config("application.day_limit");

    $start = strtotime($date);
    $end = strtotime($curdate);
    $num_days = 0;

    if($end > $start)
      $num_days = $end - $start;
    else
      $num_days = $start - $end;

    $num_days = $num_days / 60 / 60 / 24;

    return $num_days > $limit;
  }

  static function terminal_get_width(Doctrine_Collection $results, $field){
    $results_array = $results->toArray();
    
    $max_width = 0;
    foreach($results_array as $item){
      $curlen = strlen($item[$field]);
      if($max_width < $curlen){
        $max_width = $curlen;
      }
    }
    
    // add 2 spaces to the width
    $max_width += 2;
    
    return $max_width;
  }
  
  static function terminal_build_command_array($args){
    set_time_limit(5);
  
    $formatted_args = array();
    $tmp_formatted_args = array();
    
    $arg_literal_pattern = '/^\-[A-Za-z]+$/';
    
    for($ctr = 0 ; $ctr < count($args) ; $ctr ++){
      // check if this item is an arg literal
      //echo 'testing: '. $args[$ctr] .'<br />';
      if(preg_match($arg_literal_pattern, $args[$ctr])){
        // initialize
        $current_arg = $args[$ctr];
        $tmp_formatted_args[$ctr]['literal']  = $current_arg;
        $tmp_formatted_args[$ctr]['args']     = util::terminal_fetch_params(
          $args, $ctr + 1, $arg_literal_pattern
        );
      }
    }
    
    // rebuild...
    foreach($tmp_formatted_args as $tmp_arg){
      $formatted_args[$tmp_arg['literal']] = $tmp_arg['args'];
    }
    
    return $formatted_args;
  }
  
  static function terminal_fetch_params($args, $starting_index, $literal_pattern){
    $null_param = true;
    $ctr = $starting_index;
  
    // check if there's an item in the starting_index
    if(!isset($args[$ctr]))
      return $null_param;
  
    // check if the item in starting_index is a literal
    if(preg_match($literal_pattern, $args[$ctr]))
      return $null_param;
      
    // iterate until we reach the end of the collection
    // or reach another literal
    $params = null;
    while(isset($args[$ctr]) && !preg_match($literal_pattern, $args[$ctr])){
      $params .= $args[$ctr].' ';
      $ctr ++;
    }
    
    $params = rtrim($params);
    
    return $params;
  }
  
  static function micro_date($date){
    if($date == null)
      return '____';
  
    $formatted_date = strtotime($date);
    $formatted_date = date('md',$formatted_date);
  
    return $formatted_date;
  }
}