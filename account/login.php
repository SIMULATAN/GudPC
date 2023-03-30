<?php
	include_once "../scaffolding/heading.php";

	if (isset($_SESSION["user"])) {
		echo '<script>window.location.href = "/GudPC/";</script>';
	}

	if (isset($_POST["email"]) && isset($_POST["password"])) {
		$email = $_POST["email"];
		$password = $_POST["password"];

		$result = tryLogin($email, $password);
		if ($result) {
			$_SESSION["user"] = $result;
			echo '<script>window.location.href = "/GudPC/";</script>';
		}
	}
?>
<link rel="stylesheet" href="../css/login.css">
<div class="panel header" data-aos="fade-up">
	<div class="panel_inner login">
		<h1 class="headline">Login</h1>
		<form action="?action=login" method="post" class="login-form">
			<label for="email">Email</label>
			<input type="email" name="email" placeholder="Email" required>
			<label for="password">Password</label>
			<input type="password" name="password" placeholder="Password" required>
			<input type="submit" value="Login">
		</form>
		<div class="login-bottom-text">
			<a href="register.php">No account yet?</a>
			<div></div>
		</div>
		<?php
			if (isset($result) && $result == null) {
				echo '<p class="login-error">Invalid email or password</p>';
			}
		?>
	</div>
</div>
<?php
	include_once "../scaffolding/footer.php";
?>
