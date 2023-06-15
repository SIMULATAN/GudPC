<?php
	class Config {
		public string $root_path;
		public string $db_connection_string;
	}

	// parse config from config.ini
	$config = new Config();
	$ini = parse_ini_file("config.ini");
	$config->root_path = $ini["root_path"];
	$config->db_connection_string = $ini["db_connection_string"];

	global $config;
