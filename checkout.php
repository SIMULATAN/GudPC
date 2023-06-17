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

    $totalPrice = 0;
    foreach ($cart as $item) {
        $price = $item['quantity'] * $item['price'];
        $totalPrice += $price;
    }
    pg_query($dbconn, "BEGIN");
    pg_insert($dbconn, "checkout", array(
        "user_id" => $_SESSION["user"]->id,
        "date" => date("Y-m-d H:i:s"),
        "total_price" => $totalPrice
    ));
    pg_query_params($dbconn, "DELETE FROM cart_item WHERE user_id = $1", array($_SESSION["user"]->id));
    pg_query($dbconn, "COMMIT");
?>

<script>updateCartCount(0)</script>

<link href="css/checkout.css" rel="stylesheet">
<style>
    #checkout > * {
        text-align: center;
    }
</style>

<div class="full_height">
	<div id="checkout" style="display: flex; justify-content: center; align-items: center">
        <h1>Thanks for your purchase!</h1>
        <p>You will never receive your product. Lol get scammed</p>
        <a class="button cart_shop_button" style="margin-top: 0.5%" href="payment-history.php">See payment history</a>
	</div>
</div>
