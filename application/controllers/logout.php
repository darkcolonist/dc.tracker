<?php defined('SYSPATH') OR die('No direct access allowed.');
class Logout_Controller extends Controller {

  public function index() {
    $session = Session::instance();

    $session->destroy();

    url::redirect("login");
  }

}