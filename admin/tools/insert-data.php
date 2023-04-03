<?php
	// read csv file into objects
	$gpus_array = array_map('str_getcsv', file('data/gpus.csv'));
	$gpu_header = array_shift($gpus_array);
	$gpus = array();
	foreach ($gpus_array as $row) {
		$row = array_map('trim', $row);
		$gpus[] = array_combine($gpu_header, $row);
	}
	// remove header row
	array_shift($gpus_array);

	global $dbconn;
	$dbconn = pg_connect("host=postgres port=5432 dbname=db user=app password=app");

	foreach ($gpus as $gpu) {
		var_dump($gpu);

		// parse something like "$1,289.99"
		$price = floatval(str_replace("$", "", str_replace(",", "", $gpu["price"])));
		$memory = intval(str_replace("GB", "", $gpu["memory"]));
		if (strpos($gpu["clock_speed"], "GHz") !== false)
			$clock_speed = floatval(str_replace("GHz", "", $gpu["clock_speed"]));
		else
			$clock_speed = floatval(str_replace("MHz", "", $gpu["clock_speed"])) / 1000;

		$result = pg_insert($dbconn, "gpu", array(
			"name" => $gpu["name"],
			"price" => $price,
			"memory" => $memory,
			"frequency" => $clock_speed
		));
		break;
	}

?>
