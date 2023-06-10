<?php
	function goDie($message = "", $code = 500)
	{
		if ($message == "") {
			$message = "HTTP $code";
		}
		http_response_code($code);
		echo $message;
		die();
	}

	if (!isset($_GET["action"])) {
		goDie("Type parameter missing!", 400);
	}

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

	require_once "../scaffolding/db/cart.php";

	$dbconn = pg_connect($config->db_connection_string);

	$action = $_GET["action"];
	if ($action == "list") {
		echo json_encode(listCart($dbconn, $user->id));
	} elseif ($action == "add") {
		if (!isset($_GET["product_id"])) {
			goDie("Product ID parameter missing!", 400);
		}
		$result = addProductToCart($dbconn, $user->id, $_GET["product_id"]);
		http_response_code($result ? 200 : 500);
		echo getCartCount($dbconn, $user->id);
	} elseif ($action == "remove") {
		if (!isset($_GET["product_id"])) {
			goDie("Product ID parameter missing!", 400);
		}
		$result = removeProductFromCart($dbconn, $user->id, $_GET["product_id"]);
		http_response_code($result ? 200 : 500);
		echo getCartCount($dbconn, $user->id);
	} elseif ($action == "clear") {
		goDie("", clearCart($dbconn, $user->id) ? 200 : 500);
	} elseif ($action == "quantity") {
		if (!isset($_GET["product_id"])) {
			goDie("Product ID parameter missing!", 400);
		}
		if (!isset($_GET["quantity"])) {
			goDie("Quantity parameter missing!", 400);
		}
		$result = setProductQuantity($dbconn, $user->id, $_GET["product_id"], $_GET["quantity"]);
		http_response_code($result ? 200 : 500);
		echo getCartCount($dbconn, $user->id);
	} else {
		goDie("Invalid action!", 400);
	}
