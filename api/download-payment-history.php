<?php
	require_once "../scaffolding/db/account.php";

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	require_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

	$user = $_SESSION["user"] ?? null;
	if (!isset($user)) {
		goDie("401 Unauthorized", 401);
	}

	global $dbconn;
	$dbconn = pg_connect($config->db_connection_string);

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: 0");
	header('Content-Disposition: attachment; filename="payment-history.csv"');

	$csv = "date,amount,status\n";
	$result = pg_query_params($dbconn, "SELECT * FROM checkout WHERE user_id = $1 ORDER BY date DESC", [$user->id]);
	while ($row = pg_fetch_assoc($result)) {
		$csv .= $row["date"] . "," . $row["total_price"] . ",scammed\n";
	}

	if (function_exists('mb_strlen')) {
		$size = mb_strlen($csv, '8bit');
	} else {
		$size = strlen($csv);
	}
	header('Content-Length: ' . $size);
	header('Pragma: public');

	echo $csv;
