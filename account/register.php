<?php
	include_once "../scaffolding/heading.php";
	include_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

	if (isset($_SESSION["user"])) {
		echo "<script>window.location.href = '{$config->root_path}';</script>";
	}

	if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"])) {
		$username = $_POST["username"];
		$password = $_POST["password"];
		$email = $_POST["email"];

		require_once "../scaffolding/db/account.php";

		$result = createNewUser($email, $username, $password);
		if (!is_string($result)) {
			$_SESSION["user"] = $result;
			echo "<script>window.location.href = '{$config->root_path}';</script>";
		}
	}
?>
<link rel="stylesheet" href="../css/login.css">
<div class="panel header" data-aos="fade-up">
	<div class="panel_inner no-hover box-padding">
		<h1 class="headline">Register</h1>
		<form action="?action=register" method="post" class="login-form">
			<label for="username">Username</label>
			<input type="text" name="username" placeholder="Username" required value="<?php echo $username ?? '' ?>">
			<label for="email">Email</label>
			<input type="email" name="email" placeholder="Email" required value="<?php echo $email ?? '' ?>">
			<label for="password">Password</label>
			<input type="password" name="password" placeholder="Password" required
				   value="<?php echo $password ?? '' ?>">
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
