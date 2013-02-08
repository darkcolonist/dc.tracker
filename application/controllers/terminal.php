<?php defined('SYSPATH') OR die('No direct access allowed.');
class Terminal_Controller extends Protected_Template_Controller {

  // Set the name of the template to use
  public $template = 'template_terminal';
  
  // error messages
  protected $_missing_param = 'missing parameter.';

  function __construct() {
    parent::__construct();

    $this->template->title = Kohana::config("application.title");
    $this->template->styles = html::stylesheet("styles/jquery.terminal");
    $this->template->scripts = html::script("scripts/jquery-1.7.1.min");
    $this->template->scripts .= html::script("scripts/jquery.mousewheel-min");
    $this->template->scripts .= html::script("scripts/jquery.terminal-0.4.11.min");

    $this->template->meta = html::link("fatypecon.ico", "icon", "image/x-icon");
  }

  public function index() {
    $this->template->title .= ' - Madcoder Style';
  }

  protected function _response($result, $id, $error){
    return json_encode(array("jsonrpc" => "2.0",
          'result' => $result,
          'id' => $id,
          'error'=> $error));
  }

  public function command(){
    $this->auto_render = false;
    
    if(isset($GLOBALS['HTTP_RAW_POST_DATA']))
      $data = $GLOBALS['HTTP_RAW_POST_DATA'];
    else
      $data = file_get_contents('php://input');
    
    $data = json_decode($data, true);

    $the_function = 'command_'.$data['method'];
    
    if(method_exists($this, $the_function)){
      $return_val = $this->$the_function($data['params']);
      echo $this->_response($return_val, 1, null);
    }else{
      echo $this->_response(array(
          'error' => 'command \''.$data['method'].'\' unrecognized. type \'help\' for reference.'
      ), 1, null);
    }
  }

  function command_rm($arg){
    if(!count($arg))
      return $this->_missing_param;
  
    $hash = $arg[0];
    $record = Doctrine::getTable('TblTaskLines')->findOneByhash_key($hash);
    
    if($record == null)
      return 'could not locate: '.$hash;
  
    $record->delete();
  
    return $arg[0].' has been flushed.';
  }
  
  protected function set_num_hash($array){
    $encoded = json_encode($array);
    
    $this->session->set('num_hash', $encoded);
  }
  
  protected function get_num_hash(){
    $num_hash_raw = $this->session->get('num_hash');
    $num_hash = json_decode($num_hash_raw, true);
    
    return $num_hash;
  }
  
  protected function get_hash($str){
    $the_hash = $str;
    $num_hash = $this->get_num_hash();
    
    if(!empty($num_hash['_'.$str])){
      $the_hash = $num_hash['_'.$str];
    }
    
    return $the_hash;
  }

  protected function _ls_format($results){
    if($results->count() > 0){
      $list = array();
      $num_hash = array();

      $max_group_width = util::terminal_get_width($results, 'group_name');

      $list[' __#__hash'] =
        str_pad('_grp', $max_group_width, '_')
        .'_stat__'
        .'_date______'
        .'_desc_________________________'
      ;

      foreach($results as $ctr => $result){
        $ctr = $ctr + 1;
        $num_hash['_'.$ctr] = $result->hash_key;

        if($result->is_pinned)
          $le_status = '*'.$result->status.'*';
        else
          $le_status = '['.$result->status.']';

        $ctrval = str_pad($ctr, 3, ' ', STR_PAD_LEFT);
        $list[' '.$ctrval.' '.$result->hash_key] =
          str_pad($result->group_name, $max_group_width)
          .$le_status.' '
          .'c'.util::micro_date($result->date_created)
          .'m'.util::micro_date($result->date_modified) . ' '
          .$result->description;
      }
      $this->set_num_hash($num_hash);
      return $list;
    }else{
      return 'no records found.';
    }
  }

  function command_ls($args){
    $model = new TblTaskLines();

    // format the args
    $args = util::terminal_build_command_array($args);
    
    $results = $model->terminal_list($args);

    return $this->_ls_format($results);
  }
  
  function command_lm($args){
    $model = new TblTaskLines();

    if(!isset($args[0]))
      return $this->_missing_param;

    if($args[0] != 'today' 
      && $args[0] != 'week'
      && $args[0] != 'last')
      return 'invalid argument: '.$args[0];

    $args[1] = isset($args[1]) ? $args[1] : null;

    if($args[0] == 'last' && is_null($args[1])){
      return 'missing parameters.';
    }else if($args[0] == 'last' && !is_null($args[1])){
      $results = $model->terminal_list_macro_last($args[1]);
    }else{
      $results = $model->terminal_list_macro($args[0], $args[1]);
    }

    return $this->_ls_format($results);
  }
  
  function command_mv($args){
    if(!isset($args[0]))
      return $this->_missing_param;
    
    $hash = $this->get_hash($args[0]);

    // format the args
    $args = util::terminal_build_command_array($args);
    
    $model = new TblTaskLines();
    $record = Doctrine::getTable('TblTaskLines')->findOneByhash_key($hash);
    
    if($record == null)
      return 'could not locate: '.$hash;
  
    $summary = $model->terminal_edit($record, $args);
  
    return $summary;
  }
  
  function command_mk($args){
    // format the args
    $args = util::terminal_build_command_array($args);

    if(!isset($args['-g']) || 
       !isset($args['-d']))
      return $this->_missing_param;
    
    $model = new TblTaskLines();
    $model->hash_key = strtolower(text::random("alnum", 5));
    $model->group_name = $args['-g'];
    $model->description = $args['-d'];
    
    if(isset($args['-s']))
      $model->status = $args['-s'];
    
    if(isset($args['-p']))
      $model->is_pinned = $args['-p'];
    
    $model->save();
    
    $summary['message'] = 'new task created';
    $summary['hash'] = $model->hash_key;
    $summary['group'] = $model->group_name;
    $summary['description'] = $model->description;
    
    return $summary;
  }
  
  function command_type($args){
    if(empty($args[0])) return $this->_missing_param;
  
    $hash = $this->get_hash($args[0]);
    $record = Doctrine::getTable('TblTaskLines')->findOneByhash_key($hash);
    
    if($record == null) return 'could not find: '.$hash;

    $array = $record->toArray();
    
    unset($array['id']);
    unset($array['raw_command']);

    $formatted_array = array();
    foreach($array as $key => $item){
      $key = str_pad($key, 15, ' ');
      $key = str_replace('_', ' ', $key);
      $key = '  '.$key;
      $formatted_array[$key] = $item;
    }

    return $formatted_array;
  }
  
  function command_man(){
    return array(
      '_ls_' => '[listing] -g : group | -q : query | -s : status'
              .' | -o : sort order | -r : range d1,d2 | -p : pinned'
              .' | -h : hash | -x : hide group',
      '_lm_' => '[listing macros] week : all tasks that have been modified from 7 days'
              .' | today : all tasks that have been modified from today',
      '_mv_' => '[edit mode] <arg> | -g : group | -s : status | -d : description | -p : pinned',
      '_mk_' => '[make mode] <arg> | -g : group | -s : status | -d : description | -p : pinned',
      '_rm_' => '[del mode] <arg>',
      '_type_' => '[view mode] <arg>',
    );
  }
  
  function command_help(){
    return array(
        'owner' => 'this program was created by Christian Noel Reyes (christian.noel.reyes@gmail.com).',
        'message' => 'don\'t know what to do here? go to google.com or better yet GROW A SACK AND GO CRY TO YOUR MOMMY!');
  }
}