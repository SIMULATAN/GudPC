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
	function createPanel($name, $cpu, $gpu, $motherboard, $ram, $storage)
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

	$filters = array();

    // save products array into JavaScript
    echo "<script>";
    echo "const products = " . json_encode($products) . ";";
    echo "</script>";
?>

<script>
    let filters = {};

    function changeFilter(event, type, value) {
        if (event.target.checked) {
            if (filters[type] === undefined) {
                filters[type] = [];
            }
            filters[type].push(value);
        } else {
            filters[type].splice(filters[type].indexOf(value), 1);
            if (filters[type].length === 0) {
                delete filters[type];
            }
        }
        populateProducts()
    }

    function addToCart(product_id) {
        fetch("api/cart.php?action=add&product_id=" + product_id)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Failed to add to cart!");
                }
                return response.text();
            })
            .then(data => {
                console.log(data);
                updateCartCount(data);
            })
            .catch(error => {
                console.error(error);
                alert("Failed to add to cart!");
            });
    }
</script>

<div class="full_height">
    <div class="shoppanel_wrapper">
        <div class="filters_wrapper">
            <div class="filters_filter_wrapper">
				<?php
					$available_filters = array();
					foreach ($products as &$product) {
						foreach ($product as $key => $value) {
							if (str_ends_with($key, "_id")) {
								$key = substr($key, 0, -3);
								if (!array_key_exists($key, $available_filters)) {
									$available_filters[$key] = array();
								}
								// reassign with the actual value instead of the id
								$value = $product[$key];
								if (!in_array($value, $available_filters[$key])) {
									$available_filters[$key][] = $value;
								}
							}
						}
					}

					foreach ($available_filters as $key => $values) {
						echo "<div class='filters_filter_key'>$key</div>";
						foreach ($values as $value) {
							echo "<div class='filters_line_wrapper'>";
							echo "<input type='checkbox' onchange='changeFilter(event, \"$key\", \"$value\")'>";
							echo "<div>$value</div>";
							echo "</div>";
						}
					}
				?>
            </div>
        </div>
        <div class="results_wrapper">
            <div class="results_products_grid" id="results_products_grid"></div>
            <script defer>
                function populateProducts() {
                    document.getElementById("results_products_grid").innerHTML = "";
                    test: for (let product of products) {
                        if (Object.keys(filters).length > 0) {
                            for (const productElement of Object.entries(product)) {
                                const [key, value] = productElement;
                                if (filters[key] !== undefined && !filters[key].includes(value)) {
                                    continue test;
                                }
                            }
                        }
                        let panel = document.createElement("div");
                        panel.classList.add("products_panel_product_panel");
                        panel.classList.add("panel_wrapper_inner");
                        panel.innerHTML = `
                            <div>
                                <h1>${product.name}</h1>
                                <p>CPU: ${product.cpu}</p>
                                <p>GPU: ${product.gpu}</p>
                                <p>Motherboard: ${product.motherboard}</p>
                                <p>RAM: ${product.ram}</p>
                                <p>Storage: ${product.storage}</p>
                            </div>
                            <button class="add-to-cart-button" onclick="addToCart('${product.id}')">Add to cart</button>
                        `;
                        document.getElementById("results_products_grid").appendChild(panel);
                    }
                }

                document.addEventListener("DOMContentLoaded", populateProducts);
            </script>
        </div>
    </div>
</div>

<?php
	include("scaffolding/footer.php");
?>
