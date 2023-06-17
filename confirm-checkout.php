<?php
	include_once "scaffolding/heading.php";

	include_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

	if (!isset($_SESSION["user"])) {
		echo "<script>window.location.href = '$config->root_path/account/login.php';</script>";
		die();
	}

	$dbconn = pg_connect($config->db_connection_string);

	require_once "scaffolding/db/cart.php";
	$cartCount = getCartCount($dbconn, $_SESSION["user"]->id);

	if ($cartCount == 0) {
		echo "<script>window.location.href = '$config->root_path/shop.php';</script>";
		die();
	}

	$cart = listCart($dbconn, $_SESSION["user"]->id);
?>

<link href="css/checkout.css" rel="stylesheet">

<div class="full_height">
    <div id="checkout">
        <h1>Confirm Checkout</h1>
		<?php
            $totalPrice = 0;
			foreach ($cart as $item) {
				echo "<div class=\"checkout_entry\">";
				echo "<p>${item['name']}</p>";
				$price = $item['quantity'] * $item['price'];
                $totalPrice += $price;
				echo "<p>${item['quantity']} x ${item['price']}€ = ${price}€</p>";
				echo "</div>";
			}
            echo "<p style='width: 100%; text-align: right'>Total: ${totalPrice}€</p>";
		?>
        <div style="display: flex; justify-content: center; width: 100%">
            <a class="button" style="margin-top: 1%" href="checkout.php">Confirm Purchase</a>
        </div>
    </div>
</div>
