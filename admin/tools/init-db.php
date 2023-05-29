<?php
	class Migrator
	{
		# const in a class because shitty ass php doesn't allow final variables outside of classes lmao
		const currentVersion = 1;
	}

	# I despise PHP for this like what the fuck
	require_once "../../config/config.php";

	global $config;

	global $dbconn;
	$dbconn = pg_connect($config->db_connection_string);

	// create tables
	$create_tables = file_get_contents("sql/create.sql");
	// begin transaction
	pg_query($dbconn, "BEGIN");
	$result = pg_query($dbconn, $create_tables);
	// check if it worked
	if ($result) {
		echo "Database initialized - inserting version....\n";
		$newVersion = Migrator::currentVersion;
		pg_exec($dbconn, "INSERT INTO migrations VALUES ($newVersion)");
		echo "Migration version inserted :) comitting now...\n";
		pg_exec($dbconn, "COMMIT");
		echo "Commit done les gooo\n";
	} else {
		echo "\nDatabase initialization FAILED - make sure this truly is the first ever run!\n";
		pg_exec($dbconn, "ROLLBACK");
	}
