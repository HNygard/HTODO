<?php

include "mysql.php";

?><!doctype html>
<html>
  <head>
    <script type="text/javascript" src="jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="jquery-ui-1.8.1.custom.min.js"></script>

    <script type="text/javascript" src="jtodo.js"></script>

<style>
li {
 border: 1px solid black;
}

.level
{
	background-color: lightblue;
	width: 70px;
	float: right;
	#display: none;
}
.id_display
{
	background-color: pink;
	width: 70px;
	float: right;
}
.parent_id
{
	background-color: lightgreen;
	width: 70px;
	float: right;
}
.position
{
	background-color: yellow;
	width: 70px;
	float: right;
}
.finisheddisplay
{
	background-color: orange;
	width: 70px;
	float: right;
}
.finishedvalue
{
	background-color: orange;
	width: 70px;
	float: right;
	display: none;
}
.hiddenstatus
{
	background-color: gray;
	width: 15px;
	float: right;
}
.hidden
{
	height: 5px;
	display: none;
}
.nothidden
{
	height: 15px;
}
.task { margin: 5px; padding: 5px; width: 400px;}
</style>
<style type="text/css">
#tasks
{
	list-style-type: none;
	margin: 0;
	padding: 0;
	width: 80%;
}
#tasks li
{
	margin: 0 5px 5px 5px;
	padding: 5px;
	font-size: 1.1em;
	height: 1.5em;
}
html>body #tasks li
{
	height: 1.5em;
	line-height: 1.2em;
}
.ui-state-highlight
{
	height: 1.5em;
	line-height: 1.2em;
}
.sorter
{
	float: left;
	background-image:url("http://www.gstatic.com/tasks/embed-tasks-sprites9.png");
	cursor:-moz-grab;
	vertical-align:top;
	-moz-user-select:none;
	background-position:-254px 0;
	background-repeat:repeat-y;
	width:8px;
	height: 100%;
	margin-right: 5px;
}
.finish
{
	width: 16px;
	height: 16px;
	-moz-user-select:none;
	background-image:url("http://www.gstatic.com/tasks/embed-tasks-sprites9.png");
	margin-top:0;
	float: left;
	cursor: pointer;
}
.notfinished
{
	background-position:-16px -2px;
}
.finished
{
	background-position:-32px -2px;
}
.task
{
	position: relative;
	top: 3px;
	display: inline;
}
.taskNotfinished
{
	border: 1px solid black;
	color: black;
}
.taskFinished
{
	color:#808080;
	border:1px dashed #808080;
	text-decoration:line-through;
}
.workingIcon
{
	bottom:0;
	direction:ltr;
	height:32px;
	left:auto;
	position:fixed;
	right:0;
	z-index:99;
	width: 32px;
	padding: 5px;
	display: none;
}
</style>
<script type="text/javascript">
$(function() {
	$("#tasks").sortable({
		placeholder: 'ui-state-highlight',
		distance: 10,
		handle: 'div.sorter',
		update: function() {
			updateTask ();
			executeDBQueries(function (msg) { } );
		}
	});
	//$("#tasks").disableSelection();
});
</script>

  </head>
  <body>
<div id="workingIcon" class="workingIcon"><img width="32" height="32" src="loading.gif"></div>
<div id="tester"></div>
<div class="level">Level</div><br>
<div class="id_display">Id</div><br>
<div class="parent_id">Parentid</div><br>
<div class="finisheddisplay">Finisheddisplay</div><br>
<div class="finishedvalue">Finished</div>
<ul id="tasks">
<?php
/*
Old test lines:
	<li id="task1" style="margin-left: 40px;"><div class="sorter"></div><div class="finish notfinished"></div><div class="level">1</div><div class="id_display">1</div><div class="parent_id">0</div><div class="position">1</div><div class="finisheddisplay">0 %</div><div class="finishedvalue">-1</div><div class="task taskNotfinished" id="1" contenteditable="">Oppgave</div></li>
	<li id="task2" style="margin-left: 40px;"><div class="sorter"></div><div class="finish notfinished"></div><div class="level">1</div><div class="id_display">2</div><div class="parent_id">0</div><div class="position">2</div><div class="finisheddisplay">0 %</div><div class="finishedvalue">-1</div><div class="task taskNotfinished" id="2" contenteditable="">Oppgave 2</div></li>
*/

$query = mysql_query('select * from `tasks` order by `position`');
$parents = array();
$tasks = array();
while($R = mysql_fetch_assoc($query))
{
	if(!isset($parents[$R['parent']]))
	{
		$parents[$R['parent']] = array();
	}
	
	$parents[$R['parent']][] = $R['id'];
	
	$tasks[$R['id']] = $R;
}

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
	
	echo '	<li id="task'.$R['id'].'" style="margin-left: '.(40*$level).'px;'.$task_css_style.'">'.
		'<div class="sorter"'.$div_css_style.'></div>'.
		'<div class="finish '.$finished_class.'"'.$div_css_style.'></div>'.
		'<div class="level"'.$div_css_style.'>'.$level.'</div>'.
		'<div class="id_display"'.$div_css_style.'>'.$R['id'].'</div>'.
		'<div class="parent_id"'.$div_css_style.'>'.$R['parent'].'</div>'.
		'<div class="position"'.$div_css_style.'>'.$R['position'].'</div>'.
		'<div class="finisheddisplay"'.$div_css_style.'>'.$finisheddisplay.' %</div>'.
		'<div class="finishedvalue">'.$R['finished'].'</div>'.
		'<div class="hiddenstatus '.$hiddenstatus.'"></div>'.
		'<div class="task '.$task_finished_class.'" id="'.$R['id'].'" contenteditable=""'.$div_css_style.'>'.$R['text'].'</div>'.
	'</li>'.chr(10);
	
	// Print the children of this task
	print_children($R['id'], $level);
	
	unset($tasks[$R['id']]);
}

print_children(0,0); // Start at level 1

?></ul>

<?php

// Printing disconnected children
if(count($tasks))
{
	echo '<h1>Disconnected children:</h1>';
	print_r($tasks);
}

?>
<div id="dbdebug"></div>
</body>
</html>
