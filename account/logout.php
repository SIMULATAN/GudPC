<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	if (isset($_SESSION["user"])) {
		unset($_SESSION["user"]);
	}

	header("Location: " . ($_SERVER["HTTP_REFERRER"] ?? "/GudPC"));
