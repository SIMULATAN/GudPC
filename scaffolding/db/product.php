<?php

	class Product
	{
		public string $name;
		public float $price;
		public string $cpu;
		public string $gpu;
		public string $motherboard;
		public string $ram;
		public string $storage;

		public static function fetchProducts(PgSql\Connection $dbconn): array
		{
			$query = "SELECT * FROM product";
			$result = pg_query($dbconn, $query);
			$products = pg_fetch_all($result, PGSQL_ASSOC);

			// fetch the foreign keys
			foreach ($products as &$product) {
				// replace all keys ending with `_id` with the actual data, fetched from the database
				foreach ($product as $key => $value) {
					if (str_ends_with($key, "_id")) {
						$foreignKey = substr($key, 0, -3);
						$result = pg_query_params($dbconn, "SELECT * FROM $foreignKey WHERE id = $1", array($value));
						$product[$foreignKey] = pg_fetch_assoc($result)["name"];
					}
				}
			}
			return $products;
		}
	}
