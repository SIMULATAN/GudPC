<?php
	function addProductToCart($dbconn, $userId, $productId)
	{
		$query = "INSERT INTO cart_item (user_id, product_id) VALUES ($1, $2)
			ON CONFLICT(user_id, product_id) DO UPDATE SET quantity = cart_item.quantity + 1";
		$result = pg_query_params($dbconn, $query, array($userId, $productId));
		if (!$result) {
			return false;
		}
		return true;
	}

	function removeProductFromCart($dbconn, $userId, $productId)
	{
		$query = "DELETE FROM cart_item WHERE user_id = $1 AND product_id = $2";
		$result = pg_query_params($dbconn, $query, array($userId, $productId));
		if (!$result) {
			return false;
		}
		return true;
	}

	function setProductQuantity($dbconn, $userId, $productId, $quantity)
	{
		if ($quantity == 0) {
			return removeProductFromCart($dbconn, $userId, $productId);
		}
		$query = "UPDATE cart_item SET quantity = $3 WHERE user_id = $1 AND product_id = $2";
		$result = pg_query_params($dbconn, $query, array($userId, $productId, $quantity));
		if (!$result) {
			return false;
		}
		return true;
	}

	function listCart($dbconn, $userId)
	{
		$query = "SELECT * FROM cart_item INNER JOIN product ON cart_item.product_id = product.id WHERE user_id = $1 ";
		$result = pg_query_params($dbconn, $query, array($userId));
		if (!$result) {
			return false;
		}
		$cart = array();
		while ($row = pg_fetch_assoc($result)) {
			$cart[] = $row;
		}
		return $cart;
	}

	function clearCart($dbconn, $userId)
	{
		$query = "DELETE FROM cart_item WHERE user_id = $1";
		$result = pg_query_params($dbconn, $query, array($userId));
		if (!$result) {
			return false;
		}
		return true;
	}

	function getCartCount($dbconn, $userId)
	{
		$query = "SELECT COALESCE(SUM(quantity), 0) FROM cart_item WHERE user_id = $1";
		$result = pg_query_params($dbconn, $query, array($userId));
		if (!$result) {
			return false;
		}
		$row = pg_fetch_row($result);
		return $row[0];
	}
