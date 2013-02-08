<?php defined('SYSPATH') OR die('No direct access allowed.');
class Login_Controller extends Template_Controller {

  // Set the name of the template to use
  public $template = 'login_content';

  function __construct() {
    parent::__construct();

    $this->session = Session::instance();

    if($this->session->get("logged_in", false) == true)
      url::redirect("./terminal");

    $this->template->title = Kohana::config("application.title") . " - authenticate";
    $this->template->styles = html::stylesheet("styles/login");
    $this->template->scripts = html::script("scripts/jquery-1.4.2.min");

    $this->template->meta = html::link("favicon.ico", "icon", "image/x-icon");
  }

  public function index() {
    if($this->input->post())
      $this->process($this->input->post("pw"));
  }

  protected function process($password_input){
    $password = md5("tracker-".$password_input);

    if($password == Kohana::config("application.password")){
      $this->session->set("logged_in", true);
      $this->build_suggestions();
      url::redirect("./terminal");
    }else
      url::redirect(url::current());
  }

  protected function build_suggestions(){
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
  }

}