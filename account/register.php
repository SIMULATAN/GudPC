<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	if (isset($_SESSION["user"])) {
		echo '<script>window.location.href = "/GudPC/";</script>';
	}

	include_once "../scaffolding/heading.php";

	if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"])) {
		$username = $_POST["username"];
		$password = $_POST["password"];
		$email = $_POST["email"];

		require_once "../scaffolding/db/account.php";

		$result = createUser($email, $username, $password);
		if (!is_string($result)) {
			$_SESSION["user"] = $result;
			echo '<script>window.location.href = "/GudPC/";</script>';
		}
	}
?>
<link rel="stylesheet" href="../css/login.css">
<div class="panel header" data-aos="fade-up">
<div class="panel_inner login">
		<h1 class="headline">Register</h1>
		<form action="?action=register" method="post" class="login-form">
			<label for="username">Username</label>
			<input type="text" name="username" placeholder="Username" required value="<?php echo $username ?? '' ?>">
			<label for="email">Email</label>
			<input type="email" name="email" placeholder="Email" required value="<?php echo $email ?? '' ?>">
			<label for="password">Password</label>
			<input type="password" name="password" placeholder="Password" required value="<?php echo $password ?? '' ?>">
			<input type="submit" value="Register">
		</form>
		<div class="login-bottom-text">
			<a href="login.php">Login</a>
			<div></div>
		</div>
		<?php
			if (isset($result) && is_string($result)) {
				echo '<p class="login-error">' . $result . '</p>';
			}
		?>
	</div>
</div>
<?php
	include_once "../scaffolding/footer.php";
?>
