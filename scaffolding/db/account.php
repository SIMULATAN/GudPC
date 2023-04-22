<?php
	include_once "connection.php";

	date_default_timezone_set("Europe/Vienna");

	include_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

	enum ProfilePictureType {
		case EMPTY;
		case LETTER;
		case UPLOAD;
		case GRAVATAR;

		public static function fromString(string $str): ProfilePictureType|null {
			return match (strtolower($str)) {
				"empty" => ProfilePictureType::EMPTY,
				"letter" => ProfilePictureType::LETTER,
				"upload" => ProfilePictureType::UPLOAD,
				"gravatar" => ProfilePictureType::GRAVATAR,
				default => null
			};
		}
	}

	class UserProfilePicture {
		private User $user;
		public ProfilePictureType|null $type;

		public function __construct(User $user, ProfilePictureType|null $type) {
			$this->user = $user;
			$this->type = $type;
		}

		public function getUrl(): string {
			global $config;
			return match ($this->type) {
				ProfilePictureType::EMPTY => $config->root . "/res/generic-person.svg",
				ProfilePictureType::LETTER => $config->root . "/api/profile-picture-letter.php?username=" . $this->user->username,
				ProfilePictureType::UPLOAD => $config->root . "/uploads/profile/" . md5($this->user->id) . ".jpg",
				ProfilePictureType::GRAVATAR => "https://www.gravatar.com/avatar/" . md5($this->user->email) . "?d=mp&s=200",
				default => $config->root . "/res/unknown-person.svg",
			};
		}
	}

	class User
	{
		public int $id;
		public string $username;
		/// password in hashed form
		public string $password;
		public string $email;
		public DateTime $created_at;
		public DateTime $updated_at;
		public UserProfilePicture|null $profile_picture;

		public function save(): bool {
			return updateUser($this);
		}
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
			"profile_picture" => $user->profile_picture->type->name,
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
		$user->profile_picture = new UserProfilePicture($user, ProfilePictureType::fromString($row["profile_picture"] ?? "unknown"));
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
