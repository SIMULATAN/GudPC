<?php
	global $dbconn;
	$dbconn = pg_connect("host=postgres port=5432 dbname=db user=app password=app");
