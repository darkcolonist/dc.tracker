<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

  <head>

    <title><?=$title;?></title>

    <?=isset($meta)?$meta:null?>
    <?=isset($styles)?$styles:null?>
    <?=isset($scripts)?$scripts:null?>

    <script>
      jQuery(document).ready(function($) {
        $('body').terminal("<?php echo Url::base();?>terminal/command", {
          name: 'madcoder_terminal',
          greetings: "Greetings, Madcoder! What a pleasant surprise!",
          prompt: '$',
          height: 100,
          tabcompletion: 1
        });
      });
    </script>
  </head>

  <body>
  </body>
  
</html>