<?php
	class Config {
		public string $root_path;
		public string $root_url;
		public string $db_connection_string;
	}

	// parse config from config.ini
	$config = new Config();
	$ini = parse_ini_file("config.ini");
	$config->root_path = $ini["root_path"];
	$config->root_url = $ini["root_url"];
	$config->db_connection_string = $ini["db_connection_string"];

	global $config;
