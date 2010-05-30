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
 * - finished, value 0/1 => casted to boolean
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
			return array($split[0], (bool)$split[1]);
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
	elseif($split[0] == 'create')
	{
		for($i = 1; $i < count($split); $i++)
		{
			$r = matchParameters($split[$i]);
			${$r[0]} = $r[1];
		}
		
		if(!isset($parent))
			$parent = 0;
		if(!isset($position))
			$position = 0;
		
		// TODO: run against database
		
		$id = time();
		echo
			'created,'. // status
			'id:'.$id.','. // id
			'parent:'.$parent.','. // parent
			'position:'.$position; // position
	}
	elseif($split[0] == 'update')
	{
		for($i = 1; $i < count($split); $i++)
		{
			$r = matchParameters($split[$i]);
			${$r[0]} = $r[1];
		}
		
		if(!isset($parent))
			$parent = 0;
		if(!isset($position))
			$position = 0;
		
		// TODO: run against database
		
		echo
			'updated,'. // status
			'id:'.$id.','. // id
			'parent:'.$parent.','. // parent
			'position:'.$position; // position
	}
	else
	{
		echo 'lineerror 2 - '.$split[0];
	}
	
	if($this_line_num < $num_lines)
		echo chr(10);
}

?>
