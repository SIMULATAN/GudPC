<?php
	require_once "../../config/config.php";

	global $config;

	global $dbconn;
	$dbconn = pg_connect($config->db_connection_string);

	$user = readline("Please enter the user to do the operations on: ");

	$results = pg_query($dbconn, "SELECT username, id FROM users WHERE username = '$user'");
	if (pg_num_rows($results) == 0) {
		echo "User $user not found!\n";
		exit();
	}
	$userId = pg_fetch_assoc($results)["id"];

	while (($action ?? "") != "E") {
		$action = readline("Please enter the action (CRD) or E to exit: ");

		switch (strtoupper($action)) {
			case 'C':
				$permission = readline("Please enter the permission to add to the user: ");
				pg_query_params($dbconn, "INSERT INTO user_permission (user_id, permission) VALUES ($1, $2)", [$userId, $permission]);
				break;
			case 'R':
				$results = pg_query_params($dbconn, "SELECT permission FROM user_permission WHERE user_id = $1", [$userId]);
				while ($row = pg_fetch_assoc($results)) {
					echo "- {$row["permission"]}\n";
				}
				break;
			case 'D':
				$permission = readline("Please enter the permission to delete: ");
				pg_query_params($dbconn, "DELETE FROM user_permission WHERE user_id = $1 AND permission = $2", [$userId, $permission]);
				break;
			case 'E':
				break;
			default:
				echo "Invalid action!\n";
				break;
		}
	}
