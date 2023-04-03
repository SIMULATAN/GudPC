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

	pg_exec($dbconn, "CREATE TYPE storage_type AS ENUM ('ssd', 'hdd');");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS storage (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL,
		type storage_type NOT NULL,
		price NUMERIC(10,2) NOT NULL,
		capacity NUMERIC(10) NOT NULL
	);");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS cpu (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL,
		price NUMERIC(10,2) NOT NULL,
		cores NUMERIC(4) NOT NULL,
		threads NUMERIC(4) NOT NULL,
		frequency NUMERIC(2,2) NOT NULL
	);");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS gpu (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL,
		price NUMERIC(10,2) NOT NULL,
		memory NUMERIC(10) NOT NULL,
		frequency NUMERIC(2,2) NOT NULL
	);");

	// TODO: mobo

	echo "Database initialized.";
?>
