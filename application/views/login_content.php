<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

  <head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title><?=$title?></title>
    <?=isset($meta)?$meta:null?>
    <?=isset($styles)?$styles:null?>
  </head>

  <body>

    <div id="login">
      <div id="content">
        <form method="post" action="<?=url::current()?>">
          <label class="login-info">Puhi Puhi</label>
          <input disabled="disabled" class="input" name="user" type="text" value="（´・ω・｀）" />
          <label class="login-info">Entren Le Code</label>
          <input class="input" name="pw" type="password" />
          <div id="remember-forgot">
            <div class="checkbox">
              <input disabled="disabled" name="Checkbox1" type="checkbox" /></div>
            <div class="rememberme">
					More Bacon</div>
            <div id="forgot-password">
              <a>Missing Bacon</a> </div>
            <div id="login-buttton">
              <input name="Submit" src="images/login-button.jpg" type="image" value="Giriş" /></div>
          </div>
        </form>
      </div>
    </div>

  </body>

</html>
