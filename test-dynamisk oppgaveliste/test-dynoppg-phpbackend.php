<?php

if(!isset($_POST['queries']) || !is_array($_POST['queries']))
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
		else
			return array();
	}
}

foreach($_POST['queries'] as $line)
{
	$split = explode(',', $line);
	
	if(!count($split) && count($split) < 2)
	{
		echo 'lineerror 1';
	}
	elseif($split[0] == 'create')
	{
		for($i = 1; $i < count($split); $i++)
		{
			$r = matchParameters($split[$i]);
			if($r[0] == 'parent')
				$parent = $r[1];
			elseif($r[0] == 'position')
				$position = $r[1];
		}
		
		if(!isset($parent))
			$parent = 0;
		if(!isset($position))
			$position = 0;
		echo
			'created,'. // status
			'id:'.time().','. // id
			'parent:'.$parent.','. // parent
			'position:'.$position; // position
	}
	elseif($split[0] == 'update')
	{
		echo 'aight';
	}
	else
	{
		echo 'lineerror 2 - '.$split[0];
	}
	
	echo chr(10);
}

?>
