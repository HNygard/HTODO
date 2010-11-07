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

//include 'configs/mysql.php';

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
		elseif (
			$split[0] == 'hidden' ||
			$split[0] == 'removed'
		)
		{
			if($split[1] == '1')
				return array($split[0], '1');
			else
				return array($split[0], '0');
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
		$values = array();
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
				$values[$r[0]] = $r[1];
			}
		}
		
		if(!$error && $split[0] == 'create')
		{
			// Default values
			if(!isset($values['parent']))
				$values['parent'] = 0;
			if(!isset($values['position']))
				$values['position'] = 0;
			if(!isset($values['text']))
				$values['text'] = '';
			if(!isset($values['finished']))
				$values['finished'] = -1;
			if(!isset($values['hidden']))
				$values['hidden'] = '0';
			if(!isset($values['removed']))
				$values['removed'] = '0';
			
			// Checking removed and setting removed_time
			if($values['removed'] == '1')
			{
				$values['removed_time'] = time();
			}
			else
			{
				$values['removed_time'] = 0;
			}
			
			// Running query against database
			/*mysql_query("INSERT INTO `tasks`
					(
						`id` ,
						`text` ,
						`parent` ,
						`position` ,
						`finished` ,
						`hidden` ,
						`removed`
					)
					VALUES (
						NULL , 
						'".$values['text']."', 
						'".$values['parent']."', 
						'".$values['position']."', 
						'".$values['finished']."', 
						'".$values['hidden']."', 
						'".$values['removed']."'
					);
				");*/
	
			/*$values['id'] = mysql_insert_id();*/
			$values['id'] = time();
			echo
				'created,'. // status
				'id:'.$values['id'].','. // id
				'parent:'.$values['parent'].','. // parent
				'position:'.$values['position'].','. // position
				'text:"'.$values['text'].'",'. // text
				'finished:'.$values['finished'].','. // finished
				'hidden:'.$values['hidden'],','. // hidden
				'removed:'.$values['removed'],','; // removed
		}
		elseif(!$error && $split[0] == 'update')
		{
			if(!isset($values['id']))
			{
				echo 'Lineerror 4 - No ID set.';
			}
			elseif(!count($values) > 1)
			{
				echo 'Lineerror 5 - No changes.';
			}
			else
			{
				// Checking removed and setting removed_time
				if(isset($values['removed']))
				{
					if($values['removed'] == '1')
						$values['removed_time'] = time();
					else
						$values['removed_time'] = 0;
				}
				
				// Changes is in $values, building SQL
				$sql = 'UPDATE `tasks` SET ';
				$i = 0;
				foreach($values as $key => $value)
				{
					$sql .= '`'.$key.'` = \''.$value.'\'';
					$i++;
					if($i < count($values))
						$sql .= ', ';
				}
				$sql .= ' WHERE `id` = '.$values['id'];
				
				// Running query
				/*mysql_query($sql);*/
				
				// Returning status with the updated info
				echo
					'updated,'; // status
				$i = 0;
				foreach ($values as $key => $value)
				{
					echo $key.':'.$value;
					$i++;
					if($i < count($values))
						echo ',';
				}
			}
		}
	}
	
	if($this_line_num < $num_lines)
		echo chr(10);
}

?>
