<?php global $dbconn;
	$dbconn = pg_connect("host=postgres port=5432 dbname=db user=app password=app");

	// read csv file into objects
	function readCsvFileIntoObjects($filename): array {
		$lines = array_map('str_getcsv', file("data/$filename.csv"));

		$header = array_shift($lines);

		$result = array();
		foreach ($lines as $row) {
			$row = array_map('trim', $row);
			try {
				$result[] = array_combine($header, $row);
			} catch (Error $e) {}
		}
		return $result;
	}

	$gpus = readCsvFileIntoObjects("gpus");

	$insert_count = 0;
	foreach ($gpus as $gpu) {
		if (empty($gpu["model"]) || empty($gpu["chipset"]) || empty($gpu["price"]) || $gpu["price"] == "N/A" || empty($gpu["memory"]) || empty($gpu["clock_speed"]))
			continue;

		// parse something like "$1,289.99"
		$price = floatval(str_replace("$", "", str_replace(",", "", $gpu["price"])));
		$memory = intval(str_replace("GB", "", $gpu["memory"]));
		if (str_contains($gpu["clock_speed"], "GHz"))
			$clock_speed = floatval(str_replace("GHz", "", $gpu["clock_speed"])) * 1000;
		else
			$clock_speed = floatval(str_replace("MHz", "", $gpu["clock_speed"]));

		$clock_speed = round($clock_speed, 0);

		$result = pg_insert($dbconn, "gpu", array(
			"name" => $gpu["model"],
			"chipset" => $gpu["chipset"],
			"price" => $price,
			"memory" => $memory,
			"frequency" => $clock_speed
		));
		if ($result)
			$insert_count++;
	}
	echo "Inserted $insert_count GPU records.";

	$cpus = readCsvFileIntoObjects("cpus");
	foreach ($cpus as $cpu) {
		if (empty($cpu["Processor_Number"]) || empty($cpu["Recommended_Customer_Price"]) || $cpu["Recommended_Customer_Price"] == "N/A" || empty($cpu["nb_of_Cores"]) || empty($cpu["nb_of_Threads"]) || empty($cpu["Processor_Base_Frequency"]))
			continue;

		// parse something like "$1,289.99"
		$price = floatval(str_replace("$", "", str_replace(",", "", $cpu["Recommended_Customer_Price"])));
		$cores = intval($cpu["nb_of_Cores"]);
		$threads = intval($cpu["nb_of_Threads"]);
		if (str_contains($cpu["Processor_Base_Frequency"], "GHz"))
			$clock_speed = floatval(str_replace("GHz", "", $cpu["Processor_Base_Frequency"]));
		else
			$clock_speed = floatval(str_replace("MHz", "", $cpu["Processor_Base_Frequency"])) / 1000;

		$clock_speed = round($clock_speed, 1);
		if ($clock_speed == 0)
			continue;

		$result = pg_insert($dbconn, "cpu", array(
			"name" => $cpu["Processor_Number"],
			"price" => $price,
			"cores" => $cores,
			"threads" => $threads,
			"frequency" => $clock_speed
		));
		if ($result)
			$insert_count++;
	}
	echo "Inserted $insert_count CPU records.";

	$storage = readCsvFileIntoObjects("storage");

	echo "Deduping storage...";
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
	echo "Done deduping storage, moving on to inserting.";

	foreach ($storage as $storage_device) {
		if (empty($storage_device["driveName"]) || empty($storage_device["price"]) || $storage_device["price"] == "N/A" || empty($storage_device["type"]) || empty($storage_device["diskCapacity"]))
			continue;

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
	echo "Inserted $insert_count storage records.";
?>
