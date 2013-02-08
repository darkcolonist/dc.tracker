                  <small class="date" title="<?=date("l F d, Y", strtotime($day_name))?>">
                    <?=util::get_time_proximity($day_name)?>
                    <span class="technical-date">
                      <span class="day-inf"><?=" [" . date("Y-m-d", strtotime($day_name)) . "]"?></span>
                    </span>
                  </small>
                  <?foreach($day as $group_name => $group):?>
                    <?if($group_name == null):?>
                      <em class="no-group">uncategorized</em>
                    <?else:?>
                      <strong class="group"><?=$group_name?><input type="hidden"
                                                   name="meta" value="<?=$group["command"]?>" /></strong>
                    <?endif?>
                    <?foreach($group["lines"] as $task_hash => $task):?>
                      <?$special = isset($task["special"])?"sp-".$task["special"]:null?>
                      <span title="<?=$task["timestamp_string"]?>" class="task_line 
                        <?=$task["is_pinned"]?"pinned":null?>
                        <?=$special?>
                            "><small class="hash status-<?=$task["status"]?>" title="<?=$task["status"]?>"><?=$task_hash?><input
                              type="hidden"
                              name="meta" value="<?=$task["command"]?>" /></small
                        ><small class="tool tool-duplicate" title="duplicate">&nbsp;&nbsp;<input type="hidden" name="command" value="<?=$task["command_duplicate"]?>" /></small
                        ><small class="tool tool-delete" title="delete">&nbsp;&nbsp;<input type="hidden" name="command" value="<?=$task["command_delete"]?>" /></small>
                        <?=$task["task"]?></span>
                    <?endforeach?>
                  <?endforeach?>