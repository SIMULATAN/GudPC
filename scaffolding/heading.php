<?php
	require_once "db/account.php";

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	$user = $_SESSION["user"] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" href="/GudPC/style.css">
	<title>GudPC</title>
</head>
<body>
<div class="navbar_outer" data-aos="zoom-in-down" data-aos-duration="600">
	<div class="navbar">
		<a href="/GudPC" class="name headline">GudPC</a>
		<div class="navbar_links">
			<div class="cart_button navbar_button" onclick="window.location.href='/GudPC/cart.php'">
				<img src="/GudPC/res/cart.svg">
			</div>
			<div class="lang_button navbar_button" onclick="window.location.href='/GudPC/lang.php'">
				<img src="/GudPC/res/globe.svg">
			</div>
			<div class="account_link" onclick="toggleDropdown()">
				<div id="dropdown">
					<img class="account_button navbar_button" src=<?php echo ($user?->profile_picture) ?? "/GudPC/res/unknown-person.svg" ?> style="width: 3em; height: 3em; border-radius: 69%">
					<div class="dropdown_content">
						<?php
							if (isset($_SESSION["user"])) {
								echo '<a href="/GudPC/account/my-account.php">My Account</a>';
								echo '<a href="/GudPC/account/logout.php">Logout</a>';
							} else {
								echo '<a href="/GudPC/account/login.php">Login</a>';
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
		function toggleDropdown() {
			let dropdown = document.querySelector(".dropdown_content");
			if (dropdown.style.display === "flex") {
				dropdown.style.display = "none";
			} else {
				dropdown.style.display = "flex";
			}
		}
	</script>
</div>
