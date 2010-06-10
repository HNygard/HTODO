<?php

// Skript som kobler til mysql databasen og lar tilkoblingen være åpen videre i skript

// TODO: translate to english

// MySQL
$mysql_server    = 'localhost';
$mysql_db        = 'todo-test';
$mysql_username  = 'todo-test';
$mysql_passwd    = 'bbCJGfB8sDLrANP8';

// Koble til MySQL server
if(!$database = @mysql_connect($mysql_server, $mysql_username, $mysql_passwd))
{
	$smarty->assign('mysql_errno',mysql_errno());
	$smarty->assign('mysql_error',mysql_error());
	$smarty->assign('mysql_function','mysql_connect');
	$smarty->assign('error','mysql_connect');
	$smarty->display('error.tpl');
	exit();
}
if(!@mysql_select_db($mysql_db,$database))
{
	$smarty->assign('mysql_errno',mysql_errno());
	$smarty->assign('mysql_error',mysql_error());
	$smarty->assign('mysql_function','mysql_select_db');
	$smarty->assign('error','mysql_connect');
	$smarty->display('error.tpl');
	exit();
}
