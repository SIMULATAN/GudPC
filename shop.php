<?php
	include("scaffolding/heading.php");
?>
<link rel="stylesheet" href="css/shop.css">

<div class="panel header" data-aos="fade-up">
    <div class="panel_inner header_inner headline">
        <h1>Shop</h1>
        <p>Buy our crappy products and get scammed here</p>
    </div>
</div>

<?php
	function create_panel($name, $cpu, $gpu, $motherboard, $ram, $storage)
	{
		echo "<div class='products_panel_product_panel panel_wrapper_inner'>";
		echo "<h1>$name</h1>";
		echo "<p>CPU: $cpu</p>";
		echo "<p>GPU: $gpu</p>";
		echo "<p>Motherboard: $motherboard</p>";
		echo "<p>RAM: $ram</p>";
		echo "<p>Storage: $storage</p>";
		echo "</div>";
	}

	require_once "scaffolding/db/product.php";

	require_once "config/config.php";
	global $config;

	// load from database
	$dbconn = pg_connect($config->db_connection_string);

	$products = Product::fetchProducts($dbconn);

	pg_close($dbconn);
?>

<div class="full_height">
    <div class="shoppanel_wrapper">
        <div class="filters_wrapper">
            <div class="filters_filter_wrapper">
				<?php
					$filters = array();
					foreach ($products as &$product) {
						foreach ($product as $key => $value) {
							if (str_ends_with($key, "_id")) {
								$key = substr($key, 0, -3);
								if (!array_key_exists($key, $filters)) {
									$filters[$key] = array();
								}
								// reassign with the actual value instead of the id
								$value = $product[$key];
								if (!in_array($value, $filters[$key])) {
									$filters[$key][] = $value;
								}
							}
						}
					}

					foreach ($filters as $key => $values) {
						echo "<div class='filters_filter_key'>$key</div>";
						foreach ($values as $value) {
							echo "<div class='filters_line_wrapper'>";
							echo "<input type='checkbox'>";
							echo "<div>$value</div>";
							echo "</div>";
						}
					}
				?>
            </div>
        </div>
        <div class="results_wrapper">
            <div class="results_products_grid">
				<?php
					foreach ($products as &$product) {
						$title = $product["name"];
						$cpu = $product["cpu"];
						$gpu = $product["gpu"];
						$motherboard = $product["motherboard"];
						$ram = $product["ram"];
						$storage = $product["storage"];
						create_panel($title, $cpu, $gpu, $motherboard, $ram, $storage);
					}
				?>
            </div>
        </div>
    </div>
</div>

<?php
	include("scaffolding/footer.php");
?>
