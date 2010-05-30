<?php

/*
 * Format - input:
 * <action>,<param>:<value>,<param>:<value>,etc
 *
 * Optional how many parameters there are. For updates, if it's
 * not set it will not be touched in database. Creates uses some
 * default values
 * 
 * Action can be:
 * - update
 * - create
 * 
 * Param can be (input and output):
 * - position, value int
 * - parent, value int
 * - id, value int
 * - text, value text surrounded by "<value>", escaped ": ""
 * - finished, value int, -1 to 100
 */

if(isset($_GET['queries']))
{
	$_POST['queries'] = $_GET['queries'];
}

if
(
	(!isset($_POST['queries']) || !is_array($_POST['queries']))
)
{
	echo 'Error'; // TODO: send en http feilmelding heller
	exit;
}

function matchParameters($param)
{
	$split = explode(':', $param, 2);
	
	if(count($split) < 2)
	{
		return array();
	}
	else
	{
		if (
			$split[0] == 'position' || 
			$split[0] == 'parent' ||
			$split[0] == 'id'
		)
			return array($split[0], (int)$split[1]);
		elseif (
			$split[0] == 'text'
		)
			return array($split[0], $split[1]); // TODO: validate info
		elseif (
			$split[0] == 'finished'
		)
		{
			$value = (int)$split[1];
			if($value < -1 || $value > 100)
				return array();
			
			return array($split[0], $value);
		}
		else
			return array();
	}
}

$num_lines = count($_POST['queries']);
$this_line_num = 0;
foreach($_POST['queries'] as $line)
{
	$this_line_num++;
	$split = explode(',', $line); // TODO: dont use explode, must support text with ,
	
	if(!count($split) && count($split) < 2)
	{
		echo 'lineerror 1';
	}
	elseif($split[0] != 'create' && $split[0] != 'update')
	{
		echo 'lineerror 2 - '.$split[0];
	}
	else
	{
		$error = false;
		for($i = 1; $i < count($split) && !$error; $i++)
		{
			$r = matchParameters($split[$i]);
			if(!count($r))
			{
				$error = true;
				echo 'lineerror 3 - Unknown parameter: '.$split[$i];
			}
			else
			{
				${$r[0]} = $r[1];
			}
		}
		
		if(!$error && $split[0] == 'create')
		{
			// Default values
			if(!isset($parent))
				$parent = 0;
			if(!isset($position))
				$position = 0;
			if(!isset($text))
				$text = '';
			if(!isset($finished))
				$finished = -1;
	
			// Running query against database
			// TODO: run against database
	
			$id = time();
			echo
				'created,'. // status
				'id:'.$id.','. // id
				'parent:'.$parent.','. // parent
				'position:'.$position.','. // position
				'text:"'.$text.'",'. // text
				'finished:'.$finished; // finished
		}
		elseif(!$error && $split[0] == 'update')
		{
			// TODO: remove:
			if(!isset($parent))
				$parent = 0;
			if(!isset($position))
				$position = 0;
			
			// Detect changes
			// TODO: detect changes
	
			// TODO: run against database
	
			// TODO: only return updated rows
			echo
				'updated,'. // status
				'id:'.$id.','. // id
				'parent:'.$parent.','. // parent
				'position:'.$position; // position
		}
	}
	
	if($this_line_num < $num_lines)
		echo chr(10);
}

?>
