<?php
	require_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

	global $dbconn;
	$dbconn = pg_connect($config->db_connection_string);

	function logStr($str): void
	{
		echo $str . "\n";
	}

	// read csv file into objects
	function readCsvFileIntoObjects($filename): array
	{
		$lines = array_map('str_getcsv', file("data/$filename.csv"));

		$header = array_shift($lines);

		$result = array();
		foreach ($lines as $row) {
			$row = array_map('trim', $row);
			try {
				$result[] = array_combine($header, $row);
			} catch (Error $ignored) {
			}
		}
		return $result;
	}

	$gpus = readCsvFileIntoObjects("gpus");
	$length = sizeof($gpus);
	$gpus_inserted = [];

	$insert_count = 0;
	foreach ($gpus as $gpu) {
		if (empty($gpu["model"]) || in_array($gpu["model"], $gpus_inserted)
			|| empty($gpu["chipset"])
			|| empty($gpu["price"])
			|| $gpu["price"] == "N/A"
			|| empty($gpu["memory"])
			|| empty($gpu["clock_speed"])
		) {
			continue;
		}

		// parse something like "$1,289.99"
		$price = floatval(str_replace("$", "", str_replace(",", "", $gpu["price"])));
		$memory = intval(str_replace("GB", "", $gpu["memory"]));

		if (str_contains($gpu["clock_speed"], "GHz"))
			$clock_speed = floatval(str_replace("GHz", "", $gpu["clock_speed"])) * 1000;
		else
			$clock_speed = floatval(str_replace("MHz", "", $gpu["clock_speed"]));

		$clock_speed = round($clock_speed);
		if ($clock_speed <= 0)
			continue;

		$result = pg_insert($dbconn, "gpu", array(
			"name" => $gpu["model"],
			"chipset" => $gpu["chipset"],
			"price" => $price,
			"memory" => $memory,
			"frequency" => $clock_speed
		));
		if ($result) {
			$insert_count++;
			$gpus_inserted[] = $gpu["model"];
		}
	}
	logStr("Inserted $insert_count / $length GPU records.");

	$cpus = readCsvFileIntoObjects("cpus");
	$length = sizeof($cpus);
	$insert_count = 0;
	foreach ($cpus as $ram) {
		if (empty($ram["Processor_Number"])
			|| empty($ram["Recommended_Customer_Price"])
			|| $ram["Recommended_Customer_Price"] == "N/A"
			|| empty($ram["nb_of_Cores"]) || empty($ram["nb_of_Threads"])
			|| empty($ram["Processor_Base_Frequency"])
		) {
			continue;
		}

		// parse something like "$1,289.99"
		$price = floatval(str_replace("$", "", str_replace(",", "", $ram["Recommended_Customer_Price"])));
		$cores = intval($ram["nb_of_Cores"]);
		$threads = intval($ram["nb_of_Threads"]);
		if (str_contains($ram["Processor_Base_Frequency"], "GHz"))
			$clock_speed = floatval(str_replace("GHz", "", $ram["Processor_Base_Frequency"]));
		else
			$clock_speed = floatval(str_replace("MHz", "", $ram["Processor_Base_Frequency"])) / 1000;

		$clock_speed = round($clock_speed, 1);
		if ($clock_speed == 0)
			continue;

		$result = pg_insert($dbconn, "cpu", array(
			"name" => $ram["Processor_Number"],
			"price" => $price,
			"cores" => $cores,
			"threads" => $threads,
			"frequency" => $clock_speed
		));
		if ($result)
			$insert_count++;
	}
	logStr("Inserted $insert_count / $length CPU records.");

	$storage = readCsvFileIntoObjects("storage");
	$length = sizeof($storage);
	$insert_count = 0;

	logStr("Deduping storage...");
	$storage = array_reduce($storage, function ($result, $item) {
		$found = false;
		foreach ($result as &$result_item) {
			if ($result_item["driveName"] == $item["driveName"]) {
				$resultPrice = floatval($result_item["price"]);
				$itemPrice = floatval($item["price"]);
				$resultPrice = ($resultPrice + $itemPrice) / 2;
				$found = true;
				break;
			}
		}
		if (!$found)
			$result[] = $item;
		return $result;
	}, []);
	$length = sizeof($storage);
	logStr("Done deduping storage (length: $length), moving on to inserting.");

	foreach ($storage as $storage_device) {
		if (empty($storage_device["driveName"])
			|| empty($storage_device["price"])
			|| $storage_device["price"] == "N/A"
			|| empty($storage_device["type"])
			|| empty($storage_device["diskCapacity"])
		) {
			logStr("Skipping storage device: " . json_encode($storage_device));
			continue;
		}

		// parse something like "$1,289.99"
		$price = floatval(str_replace("$", "", str_replace(",", "", $storage_device["price"])));

		$capacity = $storage_device["diskCapacity"];
		$capacity = round($capacity, 0);

		$result = pg_insert($dbconn, "storage", array(
			"name" => $storage_device["driveName"],
			"price" => $price,
			"type" => $storage_device["type"],
			"capacity" => $capacity
		));
		if ($result)
			$insert_count++;
	}
	logStr("Inserted $insert_count / $length storage records.");

	$motherboards = readCsvFileIntoObjects("motherboards");
	$length = sizeof($motherboards);
	$motherboards_inserted = [];

	$insert_count = 0;
	foreach ($motherboards as $motherboard) {
		if (empty($motherboard["CATEGORY_ID"]) || $motherboard["CATEGORY_ID"] != "4"
			|| empty($motherboard["LIST_PRICE"])
			|| empty($motherboard["PRODUCT_NAME"]) || in_array($motherboard["PRODUCT_NAME"], $motherboards_inserted)
			|| empty($motherboard["DESCRIPTION - Detail 3"])
			|| empty($motherboard["DESCRIPTION - Detail 4"])
		) {
			continue;
		}

		// parse something like "$1,289.99"
		$price = floatval(str_replace("$", "", str_replace(",", "", $motherboard["LIST_PRICE"])));
		$memory_slots = intval(explode(":", $motherboard["DESCRIPTION - Detail 3"])[1]);
		if ($memory_slots <= 0)
			continue;
		$memory_max = intval(explode(":", $motherboard["DESCRIPTION - Detail 4"])[1]);
		if ($memory_max <= 0)
			continue;

		$result = pg_insert($dbconn, "motherboard", array(
			"name" => $motherboard["PRODUCT_NAME"],
			"price" => $price,
			"memory_slots" => $memory_slots,
			"memory_max" => $memory_max
		));
		if ($result) {
			$insert_count++;
			$motherboards_inserted[] = $motherboard["PRODUCT_NAME"];
		}
	}
	logStr("Inserted $insert_count / $length motherboard records.");

	$rams = readCsvFileIntoObjects("ram");
	$length = sizeof($rams);
	$insert_count = 0;
	foreach ($rams as $ram) {
		if (empty($ram["name"])
			|| empty($ram["type"])
			|| empty($ram["frequency"]) || empty($ram["capacity"])
			|| empty($ram["rgb"])
			|| empty($ram["price"])
		) {
			continue;
		}

		$price = floatval($ram["price"]);
		$frequency = intval($ram["frequency"]);
		if (str_contains($ram["capacity"], "GB"))
			$capacity = floatval(str_replace("GB", "", $ram["capacity"]));
		else {
			logStr("Unknown RAM capacity: " . $ram["capacity"]);
			continue;
		}
		$rgb = boolval($ram["rgb"]);

		$result = pg_insert($dbconn, "ram", array(
			"name" => $ram["name"],
			"type" => $ram["type"],
			"price" => $price,
			"frequency" => $frequency,
			"capacity" => $capacity,
			"rgb" => $rgb
		));
		if ($result)
			$insert_count++;
	}
	logStr("Inserted $insert_count / $length RAM records.");

	$products = readCsvFileIntoObjects("products");
	$length = sizeof($products);
	$insert_count = 0;
	foreach ($products as $product) {
		// fuck validation I made this shit myself this better be valid
		$name = $product["name"];
		$price = floatval($product["price"]);

		$cpuName = $product["cpu"];
		$result = pg_query_params($dbconn, "SELECT id FROM cpu WHERE name = $1", array($cpuName));
		if ($result && pg_num_rows($result) > 0)
			$cpu = pg_fetch_array($result)[0];

		$gpuName = $product["gpu"];
		$result = pg_query_params($dbconn, "SELECT id FROM gpu WHERE name = $1", array($gpuName));
		if ($result && pg_num_rows($result) > 0)
			$gpu = pg_fetch_array($result)[0];

		$motherboardName = $product["motherboard"];
		$result = pg_query_params($dbconn, "SELECT id FROM motherboard WHERE name = $1", array($motherboardName));
		if ($result && pg_num_rows($result) > 0)
			$motherboard = pg_fetch_array($result)[0];

		$ramName = $product["ram"];
		$result = pg_query_params($dbconn, "SELECT id FROM ram WHERE name = $1", array($ramName));
		if ($result && pg_num_rows($result) > 0)
			$ram = pg_fetch_array($result)[0];

		$storageName = $product["storage"];
		$result = pg_query_params($dbconn, "SELECT id FROM storage WHERE name = $1", array($storageName));
		if ($result && pg_num_rows($result) > 0)
			$storage = pg_fetch_array($result)[0];

		$result = pg_insert($dbconn, "product", array(
			"name" => $name,
			"price" => $price,
			"cpu_id" => $cpu,
			"gpu_id" => $gpu,
			"ram_id" => $ram,
			"storage_id" => $storage,
			"motherboard_id" => $motherboard
		));
		if ($result)
			$insert_count++;
	}
	logStr("Inserted $insert_count / $length product records.");
