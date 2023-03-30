<?php
	global $dbconn;
	$dbconn = pg_connect("host=postgres port=5432 dbname=db user=app password=app");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS users (id SERIAL PRIMARY KEY,
		username VARCHAR(255) NOT NULL UNIQUE,
		password VARCHAR(255) NOT NULL,
		email VARCHAR(255) NOT NULL UNIQUE,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    );");
