<?php

include "mysql.php";

?><!doctype html>
<html>
  <head>
    <script type="text/javascript" src="jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="jquery-ui-1.8.1.custom.min.js"></script>

    <script type="text/javascript">
next_id = 3;
$(document).ready(function()
{
	$(".task").keyup(TaskKeyup);
	$(".task").keypress(TaskKeypress);
	$(".task").focusin(TaskFocusin);
	$(".task").focusout(TaskFocusout);
	
	$(".finish").click(TaskFinished);
	
	// Debug / temp
	$("#dbdebug").click(function () {
		executeDBQueries();
	});
});

var textsavetimer = null;
task_in_focus_before = '';
tmp_new_task = new Array();
db_is_running = false;
db_run_again = false;
database_queries = new Array();
function addDBQuery (query)
{
	database_queries[database_queries.length] = query;
	
	// Debug:
	//console.log(database_queries);
	// TODO: remove
	updateDBdebug();
}

function updateDBdebug ()
{
	text = "";
	for(i = 0; i < database_queries.length; i++)
	{
		text += database_queries[i] + '<br>';
	}
	$('#dbdebug').text("").append(text);
}

function executeDBQueries (run_after)
{
	if(db_is_running)
	{
		// Run another one right afterwards
		db_run_again = true;
	}
	else if (database_queries.length > 0)
	{
		queryRunStatus(true);
		db_run_after = run_after;
		
		$.ajax({
			type: "POST",
			url: "test-dynoppg-phpbackend.php",
			data: {queries: database_queries}, // TODO: values
			success: afterDBQueries,
			// TODO:
			// error: function
		});
		database_queries = new Array(); // TODO: dont remove until success
		
		// Debug:
		$('#dbdebug').text('');
	}
}

function afterDBQueries (msg)
{
	queryRunStatus(false);
	//alert( "Data Saved: \n" + msg );
	
	db_run_after(msg);
	
	thesplit = msg.split("\n");
	for(split_i = 0; split_i < thesplit.length; split_i++)
	{
		innersplit = thesplit[split_i].split(',');
		action = '';
		new_position = 0;
		new_parent_id = 0;
		new_id = 0;
		new_finished = -1;
		new_text = '';
		for(j = 0; j < innersplit.length; j++)
		{
			innersplit2 = innersplit[j].split(':',2);
			//console.log(innersplit2);
			if(innersplit[j] == 'created' || innersplit[j] == 'updated')
			{
				action = innersplit[j];
			}
			else if(innersplit2[0] == 'position')
			{
				new_position = innersplit2[1];
			}
			else if(innersplit2[0] == 'parent')
			{
				new_parent_id = innersplit2[1];
			}
			else if(innersplit2[0] == 'id')
			{
				new_id = innersplit2[1];
			}
			else if(innersplit2[0] == 'finished')
			{
				new_finished = innersplit2[1];
			}
			else if(innersplit2[0] == 'text')
			{
				new_text = innersplit2[1].substr(1, innersplit2[1].length-2);
			}
			else
			{
				//console.log("none of the above");
			}
		}
		//console.log('Action: ' + action);
		//console.log('New_position: ' + new_position);
		//console.log('New_parent_id: ' + new_parent_id);
		//console.log('New_id: ' + new_id);
		if(action == 'created')
		{
			// Locate level
			if(new_parent_id != 0)
			{
				new_level = parseInt($('#task'+new_parent_id+' .level').text()) + 1;
			}
			else
			{
				new_level = 1;
			}
			
			// Add after
			// - We first find the parent
			// - After the parent we look at level and position
			add_after = -1;
			if(new_parent_id == 0)
				parent_found = true;
			else
				parent_found = false;
			passed_children = false;
			$('#tasks li').each(function()
			{
				if(parent_found && !passed_children)
				{
					this_id = 0;
					$.each($(this).children('.task'), function () {
						this_id = parseInt($(this).attr('id'));
					});
					this_level = 0;
					$.each($(this).children('.level'), function () {
						this_level = parseInt($(this).text());
					});
					this_position = 0;
					$.each($(this).children('.position'), function () {
						this_position = parseInt($(this).text());
					});
					if(this_level == new_level
						&& this_position < new_position)
					{
						add_after = this_id;
					}
					else if (this_level < new_level)
					{
						passed_children = true;
					}
				}
				else
				{
					this_id = 0;
					$.each($(this).children('.task'), function () {
						this_id = parseInt($(this).attr('id'));
					});
					if(this_id == new_parent_id)
					{
						add_after = this_id;
						parent_found = true;
					}
				}
			});
			
			// Make HTML
			if(new_finished == -1)
				new_finished2 = 0;
			else
				new_finished2 = new_finished;
			html = '<li id="task'+new_id+'" '+
				'style="margin-left: '+(new_level*40)+'px;">'+
				'<div class="sorter"></div>'+
				'<div class="finish notfinished"></div>'+
				'<div class="level">'+new_level+'</div>'+
				'<div class="id_display">'+new_id+'</div>' +
				'<div class="parent_id">'+new_parent_id+'</div>' +
				'<div class="position">'+new_position+'</div>' +
				'<div class="finisheddisplay">'+new_finished2+' %</div>' +
				'<div class="finishedvalue">'+new_finished+'</div>' +
				'<div '+
					// TODO: Change ID to something else
					'id="'+new_id+'" '+
					'class="task taskNotfinished" '+
					'contenteditable=""'+
				'>'+new_text+'</div></li>';
			
			// Add in the right place
			if(add_after == -1)
			{
				alert("No task added. TODO!");
				// Add inside the ul
				//$('#tasks').add(html);
			}
			else
			{
				// Add after the one we detected above
				$('#task'+add_after).after(html);
			}
			
			$('#task'+new_id+' .task').keyup(TaskKeyup);
			$('#task'+new_id+' .task').keypress(TaskKeypress);
			$('#task'+new_id+' .finish').click(TaskFinished);
			$('#'+new_id).focus(); // TODO: change id for task edit field
			
			updateTask ();
		}
		else if (action == 'updated')
		{
			
		}
		else
		{
			alert("Unknown action.\n\nLine: " + thesplit[split_i]);
		}
	}
	
	executeDBQueries(function (msg) { } );
	
	if(db_run_again)
	{
		// TODO: remove this?
		executeDBQueries(function () { });
	}
}

function addDBTextUpdate (this_id, new_text)
{
	// Update already existing line or make a new one
	
	found = false;
	for(i = 0; i < database_queries.length; i++)
	{
		if(database_queries[i].substr(0, ('update,id:'+this_id).length) == 'update,id:'+this_id)
		{
			// Update the text:
			database_queries[i] = 'update,id:'+this_id+',text:'+new_text;
			updateDBdebug(); // TODO: remove
			found = true;
		}
	}

	if(!found)
	{
		// Not found, adding query
		addDBQuery ('update,id:'+this_id+',text:'+new_text);
	}
}

function queryRunStatus(is_running)
{
	if(is_running)		
		$('#workingIcon').fadeIn();
	else
		$('#workingIcon').fadeOut();
	
	db_is_running = is_running;
	//alert("Query_is_running: " + db_is_running);
}

function TaskKeyup (e)
{
	if(e.keyCode == 13) // Enter is pressed
	{
		if(db_is_running)
		{
			alert('Database is working. Please wait.');
			return;
		}
		
		// Getting level
		next_level = parseInt($('#task'+$(this).attr('id')+ ' .level').text());
		
		// Getting parent
		parent_id = parseInt($('#task'+$(this).attr('id')+ ' .parent_id').text());
		
		// Getting position
		position = parseInt($('#task'+$(this).attr('id')+ ' .position').text()) + 1;
		
		// Creating task in database and getting id
		addDBQuery('create,parent:'+parent_id+',position:'+position);
		
		executeDBQueries(function (msg) { });
	}
	else if (e.keyCode == 9) // Tab is pressed
	{
		// Current id
		current_id = $(this).attr('id');
		
		// Finding level
		next_level = parseInt($('#task'+current_id+ ' .level').text());
		if(e.shiftKey)
		{
			if(next_level > 1) // 1 is the lowest level
				next_level--;
		}
		else
			next_level++;
		
		taskSetLevel(current_id, next_level);
		
		executeDBQueries(function (msg) { } );
	}
	else if (e.keyCode == 38 || e.keyCode == 40) // Up or down
	{
		found_focus = false;
		next = false;
		last_id = 0;
		
		// Find current id
		current_id = parseInt($(this).attr('id'));
		
		$('#tasks li').each(function()
		{
			if(found_focus)
				return;
			
			// Finding id for this task
			this_id = 0;
			$.each($(this).children('.task'), function () {
				this_id = $(this).attr('id');
			});
			if(this_id == 0)
				return; // Error
			
			if(next)
			{
				found_focus = true;
				$('#'+this_id).focus(); // TODO: change id for task edit field
			}
			
			if(e.keyCode == 38) // Up
			{
				if(this_id == current_id)
				{
					found_focus = true;
					$('#'+last_id).focus(); // TODO: change id for task edit field
				}
			}
			else // Down
			{
				if(this_id == current_id)
				{
					next = true;
				}
			}
			
			last_id = this_id;
		});
	}
	else
	{
		$("#tester").text(e.keyCode);
		
		if(task_in_focus_before != $(this).text())
		{
			addDBTextUpdate ($(this).attr('id'), $(this).text());
			
			// Setting a time for five seconds
			clearTimeout(textsavetimer);
			textsavetimer = setTimeout(function () {
				// Execute database queries
				executeDBQueries(function (msg) { } );
			}, 3000);
		}
	}
}

function TaskKeypress(e)
{
	if(e.keyCode == 13 || e.keyCode == 9) {
		return false;
	}
}

function TaskFocusin ()
{
	task_in_focus_before = $(this).text();
}

function TaskFocusout ()
{
	if(task_in_focus_before != $(this).text())
	{
		addDBTextUpdate ($(this).attr('id'), $(this).text());
		
		// Execute database queries
		executeDBQueries(function (msg) { } );
	}
}

function taskSetLevel (task_id, task_level)
{

	if(task_level >= 1)
	{
		$('#task'+task_id).css('margin-left', task_level*40+'px');
		$('#task'+task_id+ ' .level').text("").text(task_level);
		
		updateTask();
	}
}

function updateTask ()
{
	last_level = -1;
	
	levels = new Array();
	levels_parent = new Array();
	positions = new Array();
	
	levels[0] = 1;
	levels_parent[0] = 0;
	positions[0] = 0;
	
	// Running through all tasks
	$('#tasks li').each(function()
	{
		// Getting id
		this_id = 0;
		$.each($(this).children('.task'), function () {
			this_id = $(this).attr('id');
		});
		if(this_id == 0)
			return; // Error
		
		// Getting level
		this_level = 0;
		$.each($(this).children('.level'), function () {
			this_level = parseInt($(this).text());
		});
		while(levels.length > 1 && levels[levels.length-1] > this_level-1)
		{
			levels.length--;
			levels_parent.length--;
			positions.length--;
		}
		
		// Is parent changed?
		this_parent_id = 0;
		$.each($(this).children('.parent_id'), function () {
			this_parent_id = parseInt($(this).text());
		});
		if(this_parent_id != levels_parent[levels_parent.length-1])
		{
			$('#task'+this_id+ ' .parent_id').text(levels_parent[levels_parent.length-1]);
			addDBQuery('update,id:'+this_id+',parent:'+levels_parent[levels_parent.length-1]);
		}
		
		// Is position changed?
		positions[levels.length-1]++;
		this_position = 0;
		$.each($(this).children('.position'), function () {
			this_position = parseInt($(this).text());
		});
		if(this_position != positions[levels.length-1])
		{
			$(this).children('.position').text(positions[levels.length-1]);
			addDBQuery('update,id:'+this_id+',position:'+positions[levels.length-1]);
		}
		
		// Setting parent for further use
		levels_parent[levels.length]  = this_id;
		positions[levels.length]      = 0;
		levels[levels.length]         = this_level;
	});
}

function TaskFinished(e)
{
	// Find current id
	current_id = parseInt($(this).parent().children('.task').attr('id'));
	//console.log('Current: ' + current_id);
	
	if($(this).hasClass('notfinished'))
	{
		// Finish the task
		taskUpdateFinished(current_id, 100);
	}
	else
	{
		// Set it back to dependent on children
		taskUpdateFinished(current_id, -1);
	}
}

function taskUpdateFinished(task_id, finishedvalue)
{
	if(finishedvalue == 100)
	{
		// The task is finished
		$('#task'+task_id+ ' .finish').removeClass('notfinished').addClass('finished');
		$('#task'+task_id+ ' .task').removeClass('taskNotfinished').addClass('taskFinished');
		
	}
	else
	{
		// The task is not finished
		$('#task'+task_id+ ' .finish').removeClass('finished').addClass('notfinished');
		$('#task'+task_id+ ' .task').removeClass('taskFinished').addClass('taskNotfinished');
		

	}
	// Updating text
	if(finishedvalue == -1)
		$('#task'+task_id+ ' .finisheddisplay').text('0 %');
	else
		$('#task'+task_id+ ' .finisheddisplay').text(finishedvalue + ' %');
	
	// Updating the in-html saved value
	$('#task'+task_id+ ' .finishedvalue').text(finishedvalue);
	
	// Updating database
	addDBQuery('update,id:'+task_id+',finished:' + finishedvalue);
	
	// Update parents
	// TODO:
	
	// Execute database queries
	executeDBQueries(function (msg) { } );
}

</script>

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
	echo '	<li id="task'.$R['id'].'" style="margin-left: '.(40*$level).'px;">'.
		'<div class="sorter"></div>'.
		'<div class="finish '.$finished_class.'"></div>'.
		'<div class="level">'.$level.'</div>'.
		'<div class="id_display">'.$R['id'].'</div>'.
		'<div class="parent_id">'.$R['parent'].'</div>'.
		'<div class="position">'.$R['position'].'</div>'.
		'<div class="finisheddisplay">'.$finisheddisplay.' %</div>'.
		'<div class="finishedvalue">'.$R['finished'].'</div>'.
		'<div class="task '.$task_finished_class.'" id="'.$R['id'].'" contenteditable="">'.$R['text'].'</div>'.
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
	echo '<h1>Disconnected children: ';
	print_r($tasks);
	echo '</h1>';
}

?>
<div id="dbdebug"></div>
</body>
</html>
