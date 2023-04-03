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

	pg_exec($dbconn, "CREATE TYPE storage_type AS ENUM ('SSD', 'HDD');");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS storage (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL UNIQUE,
		type storage_type NOT NULL,
		price NUMERIC(12,2) NOT NULL CHECK (price > 0),
		capacity NUMERIC(10) NOT NULL CHECK (capacity > 0)
	);");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS cpu (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL UNIQUE,
		price NUMERIC(12,2) NOT NULL CHECK (price > 0),
		cores NUMERIC(4) NOT NULL CHECK (cores > 0),
		threads NUMERIC(4) NOT NULL CHECK (threads > 0),
		frequency NUMERIC(6,2) NOT NULL CHECK (frequency > 0)
	);");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS gpu (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL UNIQUE,
		chipset VARCHAR(255),
		price NUMERIC(12,2) NOT NULL CHECK (price > 0),
		memory NUMERIC(10) NOT NULL CHECK (memory > 0),
		frequency NUMERIC(6,2) NOT NULL CHECK (frequency > 0)
	);");

	// TODO: mobo

	echo "Database initialized.";
?>
