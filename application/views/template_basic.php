<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

  <head>

    <title><?=$title;?></title>

    <?=isset($meta)?$meta:null?>
    <?=isset($styles)?$styles:null?>
    <?=isset($scripts)?$scripts:null?>

  </head>

  <body>

    <div id="main-content">
      <fieldset>
        <legend><?=html::anchor(null, $title, array("title" => "home"))?>
          [<?=html::anchor("logout", "x", array("title"=>"revoke authentication (logout)"))?>]</legend>
        <form id="frmMain" action="./" method="post">
          <div>
            <input id="txtNewTask" type="text" name="txtNewTask" value="" />
            <input id="btnSubmit" type="submit" value="go" />
          </div>
        </form>
        <small>
          usage:
          <?$commands_text = null?>
          <?foreach($commands as $name => $line):?>

            <?$name .= isset($commands_help[$name]) ? ": ".$commands_help[$name] : null ?>
            <?$commands_text .= $line ."&lt;". $name ."&gt; | "?>
          <?endforeach?>
          <?$commands_text = rtrim($commands_text, " | ")?>
          <?=$commands_text?>
        </small>
        <script type ="text/javascript">
          $(document).ready(function(){
            $('input[name=txtNewTask]').autoComplete({
              ajax: '<?=url::base()?>day_task/suggest',
              multiple: true,
              multipleSeparator: ' ',
              striped: 'auto-complete-striped',
              // Add a delay as autofill takes some time
              delay: 1500,
              minChars: 2,
              maxItems: <?=kohana::config("application.suggest_limit")?>,
              width: 300,
              preventEnterSubmit: false
            }); // auto-complete / auto-suggest


            $("#frmMain").submit(function(e){
              e.preventDefault();

              $("#txtNewTask").addClass("txt-loading");

              $.ajax({
                type: "POST",
                data: $("#frmMain").serialize(),
                url: $("#frmMain").attr("action"),
                dataType: "json",
                success: function(data){
                  $("#txtNewTask").val("");

                  if(data.dates != null)
                    update_current(data.dates);
                  else if(data.url != null)
                    window.location = data.url;
                },
                error: function(){
                  window.location.reload();
                }
              }); // ajax

            }); // form submit

            function update_current(dates){
              $.ajax({
                type: "GET",
                url: "<?=url::base()?>day_task/dates/"+dates,
                dataType: "json",
                success: function(data){
                  for(var i in data){
                    var container = $("#d"+data[i].container);

                    if(container.length == 0)
                      window.location.reload();

//                    container.html(data[i].html + '<h6 style="color:red;">!</h6>');
                    container.html(data[i].html);
                  }

                  $("#txtNewTask").removeClass("txt-loading");
                }
              }); // ajax
            }

            $(".hash").live("click",function(){
              var str = null;

              str = $(this).children("input").val();

              $("#txtNewTask").val(str);
              $("#txtNewTask").focus();
            }); // hash click

            $(".tool").live("click",function(){
              var str = null;

              str = $(this).children("input").val();

              $("#txtNewTask").val(str);
              $("#txtNewTask").focus();
            }); // tool click

            $(".group").live("click",function(){
              var str = null;

              str = $(this).children("input").val();

              $("#txtNewTask").val(str);
              $("#txtNewTask").focus();
            }); // group click

            $("[rel=external]").live("click",function(e){
              e.preventDefault();

              window.open($(this).attr("href"))
            });

          });
        </script>
        <div id="tasks-list">
          <ul>

            <?if(count($days) > 0):?>
              <?$alt_class = "odd"?>
              <?foreach($days as $day_name => $day):?>
                <?$class = $day_name == date("Y-m-d")?" current":null?>
                <?$class .= $day_name > date("Y-m-d")?" planned":null?>
                <li class="<?=$alt_class?> <?=$class?> task-<?=strtolower(date("D", strtotime($day_name)))?>" id="d<?=$day_name?>">
                  <?=View::factory("day_content", array(
                      "day_name" => $day_name,
                      "day" => $day
                    ))->render()?>
                  <?$alt_class=$alt_class=="even"?"odd":"even"?>
                </li>
              <?endforeach?>
            <?else:?>
              <li>
                <em>nothing to show...</em>
              </li>
            <?endif?>

          </ul>
        </div>
      </fieldset>
    </div>
    <h4 class="footer">proudly brought to you by <strong>nyan nyan service entertainment</strong> via darkcolonist<br /><a href="http://google.com/">should i be here? =/ (click here if you feel that you do not belong, leave nao foo!)</a></h4>
  </body>
  
</html>