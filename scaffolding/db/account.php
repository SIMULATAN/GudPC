<?php
	include_once "connection.php";

	date_default_timezone_set("Europe/Vienna");

	class User {
		public int $id;
		public string $username;
		public string $password;
		public string $email;
		public DateTime $created_at;
		public DateTime $updated_at;
		public string $profile_picture;
	}

	function getUser($id): User | null {
		global $dbconn;
		$result = pg_query($dbconn, "SELECT * FROM users WHERE id = $id");

		if (!$result) {
			return null;
		}

		$row = pg_fetch_assoc($result);
		return deserializeUser($row);
	}

	function deserializeUser($row): User {
		$user = new User();
		$user->id = $row["id"];
		$user->username = $row["username"];
		$user->password = $row["password"];
		$user->email = $row["email"];
		$user->created_at = new DateTime($row["created_at"]);
		$user->updated_at = new DateTime($row["updated_at"]);
		$user->profile_picture = $row["profile_picture"] ?? "https://gravatar.com/avatar/" . md5(strtolower(trim($user->email))) . "?s=200&d=mp";
		return $user;
	}

	function tryLogin($email, $password): User | null {
		global $dbconn;
		$result = pg_query($dbconn, "SELECT * FROM users WHERE email = '$email'");

		if ($result) {
			$row = pg_fetch_assoc($result);
			if ($row && password_verify($password, $row["password"])) {
				return deserializeUser($row);
			}
		}
		return null;
	}

	function createUser($email, $username, $password): User | string {
		global $dbconn;
		if (strlen($username) < 3) {
			return "Username must be at least 3 characters long";
		}

		if (strlen($password) < 8) {
			return "Password must be at least 8 characters long";
		}

		$result = pg_query($dbconn, "SELECT * FROM users WHERE email = '$email'");
		if ($result) {
			$row = pg_fetch_assoc($result);
			if ($row) {
				return "Email already in use (<a href='login.php'>Login?</a>)";
			}
		}

		$result = pg_query($dbconn, "SELECT * FROM users WHERE username = '$username'");
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
