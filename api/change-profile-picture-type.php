<?php
	function goDie($message = "", $code = 500) {
		if ($message == "") {
			$message = "HTTP $code";
		}
		http_response_code($code);
		echo $message;
		die();
	}

	if ($_SERVER["REQUEST_METHOD"] != "POST") {
		goDie("405 Method Not Allowed", 405);
	}

	if (!isset($_GET["type"])) {
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

	$type = $_GET["type"];
	$type = ProfilePictureType::fromString($type);
	if (!isset($type)) {
		goDie("Invalid type!", 400);
	}

	$user->profile_picture->type = $type;
	$user->profile_picture->data = $type == ProfilePictureType::UPLOAD ? $_FILES["profile_picture"]["tmp_name"] : null;
	$user->save();

	echo $user->profile_picture->getUrl();
