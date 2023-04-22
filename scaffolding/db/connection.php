<?php
	global $dbconn;
	include_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

	$dbconn = pg_connect($config->db_connection_string) or die("Could not connect to database");
