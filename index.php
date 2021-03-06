<?php

include 'configs/mysql.php';
include 'configs/htodo-functions.php';

?><!doctype html>
<html>
	<head>
		<title>HTODO - Hierarchy todo list</title>
		<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.1.custom.min.js"></script>
		<script type="text/javascript" src="js/htodo.js"></script>
		<script type="text/javascript">backend_url='htodo-backend.php';</script>
		<link rel="stylesheet" type="text/css" href="css/htodo.css" />
	</head>
	<body>

<div id="workingIcon" class="workingIcon"><img width="32" height="32" src="loading.gif"></div>
<div id="tester"></div>
<ul id="tasks">
<?php

$query = mysql_query('select * from `tasks` where removed = false order by `position`');
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
<div id="unsaved">Something is not saved</div>

<div class="license"><a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/no/"><img alt="Creative Commons License" style="border-width:0" src="images/cc-by-sa-80x15.png" /></a>&nbsp;&nbsp;<span xmlns:dc="http://purl.org/dc/elements/1.1/" property="dc:title">HTODO</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://hnygard.no/" property="cc:attributionName" rel="cc:attributionURL">Hallvard Nyg&#229;rd</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/no/">Creative Commons Attribution-Share Alike 3.0 Norway License</a>. Source code can be found at <a xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://github.com/HNygard/HTODO" rel="dc:source">github.com/HNygard/HTODO</a>.</div>
</body>
</html>
