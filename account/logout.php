<?php
	require_once "../scaffolding/db/account.php";

	SESSION_START();

	// Destroy the session
	session_unset();
	session_destroy();
	session_write_close();

	header("Location: ../");
