<?php defined('SYSPATH') OR die('No direct access allowed.');
class Welcome_Controller extends Protected_Template_Controller {

  // Set the name of the template to use
  public $template = 'template_basic';

  function __construct() {
    parent::__construct();

    $this->template->title = Kohana::config("application.title");
    $this->template->styles = html::stylesheet("styles/master");
    $this->template->styles .= html::stylesheet("styles/jquery.auto-complete");
    $this->template->scripts = html::script("scripts/jquery-1.4.2.min");
    $this->template->scripts .= html::script("scripts/jquery.auto-complete");

    $this->template->meta = html::link("favicon.ico", "icon", "image/x-icon");
  }

  public function index() {
    $task = new Task();

    $search_params = $this->input->get();

    $task->process($this->input->post("txtNewTask"));

    $model = new TblTaskLines();

    $this->template->days = $task->build_days($model->get_latest($search_params));
    $this->template->commands = $task->commands;
    $this->template->commands_help = $task->commands_help;
  }

}