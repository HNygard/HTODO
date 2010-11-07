<?php


function print_children($id, $parent_level)
{
	global $parents, $tasks;
	
	if(isset($parents[$id]))
	{
		foreach ($parents[$id] as $children_id)
		{
			print_task($tasks[$children_id], $parent_level + 1);
		}
	}
}

function print_task($R, $level)
{
	global $tasks;
	
	if($R['finished'] == -1)
		$finisheddisplay = 0;
	else
		$finisheddisplay = $R['finished'];
	
	if($R['finished'] == 100)
	{
		$task_finished_class = 'taskFinished';
		$finished_class = 'finished';
	}
	else
	{
		$task_finished_class = 'taskNotfinished';
		$finished_class = 'notfinished';
	}
	
	if($R['hidden'] == '1')
	{
		$hiddenstatus    = 'hidden';
		$task_css_style  = ' height: 5px; display: list-item; padding: 0px;';
		$div_css_style   = ' style="display:none;"';
	}
	else
	{
		$hiddenstatus    = 'nothidden';
		$task_css_style  = '';
		$div_css_style   = '';
	}
	
	if($R['removed'] == '1')
	{
		$removedstatus = 'removed';
	}
	else
	{
		$removedstatus = 'notremoved';
	}
	
	echo '	<li id="task'.$R['id'].'" style="margin-left: '.(40*$level).'px;'.$task_css_style.'">'.
		'<div class="sorter"'.$div_css_style.'></div>'.
		'<div class="finish '.$finished_class.'"'.$div_css_style.'></div>'.
		'<div class="level"'.$div_css_style.'>'.$level.'</div>'.
		'<div class="id_display"'.$div_css_style.'>'.$R['id'].'</div>'.
		'<div class="parent_id"'.$div_css_style.'>'.$R['parent'].'</div>'.
		'<div class="position"'.$div_css_style.'>'.$R['position'].'</div>'.
		'<div class="finisheddisplay"'.$div_css_style.'>'.$finisheddisplay.' %</div>'.
		'<div class="finishedvalue">'.$R['finished'].'</div>'.
		'<div class="hiddenstatus '.$hiddenstatus.'">-</div>'.
		'<div class="removedstatus '.$removedstatus.'"'.$div_css_style.'>X</div>'.
		'<div class="task '.$task_finished_class.'" id="'.$R['id'].'" contenteditable=""'.$div_css_style.'>'.$R['text'].'</div>'.
	'</li>'.chr(10);
	
	// Print the children of this task
	print_children($R['id'], $level);
	
	unset($tasks[$R['id']]);
}


?>
