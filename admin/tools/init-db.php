<?php
	global $dbconn;
	$dbconn = pg_connect("host=postgres port=5432 dbname=db user=app password=app");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS users (id SERIAL PRIMARY KEY,
		username VARCHAR(255) NOT NULL UNIQUE,
		password VARCHAR(255) NOT NULL,
		email VARCHAR(255) NOT NULL UNIQUE,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		profile_picture VARCHAR(255)
	);");

	// CREATE IF NOT EXISTS is not supported in PostgreSQL (lmao)
	pg_exec($dbconn, "
		DO $$ BEGIN
			CREATE TYPE storage_type AS ENUM ('SSD', 'HDD');
		EXCEPTION
			WHEN duplicate_object THEN null;
		END $$;
	");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS storage (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL UNIQUE,
		type storage_type NOT NULL,
		price INT NOT NULL CHECK (price > 0),
		capacity INT NOT NULL CHECK (capacity > 0)
	);");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS cpu (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL UNIQUE,
		price INT NOT NULL CHECK (price > 0),
		cores INT NOT NULL CHECK (cores > 0),
		threads INT NOT NULL CHECK (threads > 0),
		frequency INT NOT NULL CHECK (frequency > 0)
	);");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS gpu (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL UNIQUE,
		chipset VARCHAR(255),
		price INT NOT NULL CHECK (price > 0),
		memory INT NOT NULL CHECK (memory > 0),
		frequency INT NOT NULL CHECK (frequency > 0)
	);");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS motherboard (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL UNIQUE,
		price INT NOT NULL CHECK (price > 0),
		memory_slots INT NOT NULL CHECK (memory_slots > 0),
		memory_max INT NOT NULL CHECK (memory_max > 0)
	)");

	pg_exec($dbconn, "CREATE TABLE IF NOT EXISTS ram (
		id SERIAL PRIMARY KEY,
		name VARCHAR(255) NOT NULL UNIQUE,
		type VARCHAR(255) NOT NULL,
		frequency INT NOT NULL CHECK (frequency > 0),
		capacity INT NOT NULL CHECK (capacity > 0),
		rgb BOOLEAN NOT NULL,
		price INT NOT NULL CHECK (price > 0)
	);");

	echo "Database initialized.\n";
?>
