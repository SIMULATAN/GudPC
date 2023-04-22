<?php
	include_once "connection.php";

	date_default_timezone_set("Europe/Vienna");

	class User
	{
		public int $id;
		public string $username;
		/// password in hashed form
		public string $password;
		public string $email;
		public DateTime $created_at;
		public DateTime $updated_at;
		public string|null $profile_picture;
	}

	function updateUser(User $user): bool
	{
		global $dbconn;
		$user->updated_at = new DateTime();

		return pg_update($dbconn, "users", array(
			"username" => $user->username,
			"password" => $user->password,
			"email" => $user->email,
			"updated_at" => $user->updated_at->format("Y-m-d H:i:s"),
			"profile_picture" => $user->profile_picture
		), array("id" => $user->id));
	}

	function getUser($id): User|null
	{
		global $dbconn;
		$result = pg_query($dbconn, "SELECT * FROM users WHERE id = $id");

		if (!$result) {
			return null;
		}

		$row = pg_fetch_assoc($result);
		return deserializeUser($row);
	}

	function deserializeUser($row): User
	{
		$user = new User();
		$user->id = $row["id"];
		$user->username = $row["username"];
		$user->password = $row["password"];
		$user->email = $row["email"];
		$user->created_at = new DateTime($row["created_at"]);
		$user->updated_at = new DateTime($row["updated_at"]);
		$user->profile_picture = $row["profile_picture"] ?? null;
		return $user;
	}

	function tryLogin($email, $password): User|null
	{
		global $dbconn;
		$result = pg_query_params($dbconn, "SELECT * FROM users WHERE lower(email) = lower($1)", array($email));

		if ($result) {
			$row = pg_fetch_assoc($result);
			if ($row && password_verify($password, $row["password"])) {
				return deserializeUser($row);
			}
		}
		return null;
	}

	function createNewUser($email, $username, $password): User|string
	{
		$email = trim($email);
		$username = trim($username);

		global $dbconn;
		if (strlen($username) < 3) {
			return "Username must be at least 3 characters long";
		}

		if (strlen($password) < 8) {
			return "Password must be at least 8 characters long";
		}

		$result = pg_query_params($dbconn, "SELECT * FROM users WHERE lower(email) = lower($1)", array($email));
		if ($result) {
			$row = pg_fetch_assoc($result);
			if ($row) {
				return "Email already in use (<a href='login.php'>Login?</a>)";
			}
		}

		$result = pg_query_params($dbconn, "SELECT * FROM users WHERE lower(username) = lower($1)", array($username));
		if ($result) {
			$row = pg_fetch_assoc($result);
			if ($row) {
				return "Username already in use";
			}
		}

		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		$result = pg_insert($dbconn, "users", array("email" => $email, "username" => $username, "password" => $hashedPassword));

		if ($result) {
			$result = pg_query($dbconn, "SELECT * FROM users WHERE email = '$email'");
			if ($result) {
				$row = pg_fetch_assoc($result);
				if ($row) {
					return deserializeUser($row);
				}
			}
		}
		return "Something went wrong";
	}
