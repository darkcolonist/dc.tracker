<?php

defined('SYSPATH') OR die('No direct access allowed.');

class Protected_Template_Controller extends Template_Controller {
  // Set the name of the template to use
  public $template = 'blank_content';

  function __construct() {
    parent::__construct();

    $this->session = Session::instance();

    if($this->session->get("logged_in", false) == false)
      url::redirect("login");
  }

}