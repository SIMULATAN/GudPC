<?php
	include_once "../scaffolding/heading.php";

	if (!isset($_SESSION["user"])) {
		echo '<script>window.location.href = "login.php";</script>';
	}

	$user = $_SESSION["user"];

	if (isset($_POST["email"]) || isset($_POST["username"]) || isset($_POST["password"])) {
		$email = $_POST["email"];
		$username = $_POST["username"];
		$password = $_POST["password"];
		$profile_picture = $_POST["profile_picture"];

		// update existing user
		if ($user->email != null && $user->email != $email) {
			$user->email = $email;
		}
		if ($user->username != null && $user->username != $username) {
			$user->username = $username;
		}
		if ($password != null) {
			$user->password = password_hash($password, PASSWORD_DEFAULT);
		}
		if ($profile_picture != null && $profile_picture != $user->profile_picture) {
			$user->profile_picture = $profile_picture;
		}

		updateUser($user);
	}
?>
	<link rel="stylesheet" href="../css/login.css">

	<style>
		.my-account-panel {
			display: flex;
			justify-content: center;
			align-items: flex-start;
			flex-direction: row;
			gap: 10%;
		}

		.profile-pic {
			color: transparent;
			display: flex;
			justify-content: center;
			align-items: center;
			position: relative;
		}

		.profile-pic input {
			display: none;
		}

		.profile-pic img {
			position: absolute;
			object-fit: cover;
			width: 15em;
			height: 15em;
			z-index: 0;
		}

		.profile-pic .-label {
			cursor: pointer;
			height: 15em;
			width: 15em;
		}

		.profile-pic:hover .-label {
			display: flex;
			justify-content: center;
			align-items: center;
			background-color: rgba(0, 0, 0, 0.5);
			z-index: 10000;
			transition: background-color .5s ease;
			margin-bottom: 0;
		}

		.profile-pic span {
			display: inline-flex;
			padding: .2em;
			height: 2em;
		}
	</style>

	<script>
        let changeProfilePicture = function (event) {
            // send multiform data to server
            let formData = new FormData();
            formData.append("profile_picture", event.target.files[0]);
            fetch("../api/upload-profile-picture.php", {
                    method: "POST",
                    body: formData
                }
            )
                .then(data => {
                    return data.text().then(text => {
                        if (data.status === 200) {
                            let images = document.getElementsByClassName("profile-picture");
                            // set from body, the URL will be the same (md5 of the user id) but the image might be different
                            for (let i = 0; i < images.length; i++) {
                                images[i].src = text + "?" + new Date().getTime();
                            }
                        } else {
                            throw new Error(`${data.status} ${data.statusText}: ${text}`);
                        }
                    });
                })
                .catch(error => {
                    console.error(error);
                    alert("Error setting profile picture: " + error);
                });
        };
	</script>

	<div class="panel header">
		<div class="panel_inner no-hover box-padding">
			<h1 class="headline">Account</h1>
			<div class="my-account-panel">
				<form action="?action=account" method="post" class="login-form">
					<label for="username">Username</label>
					<input type="text" name="username" placeholder="Username" required
						   value="<?php echo $user->username ?>">
					<label for="email">Email</label>
					<input type="email" name="email" placeholder="Email" required value="<?php echo $user->email ?>">
					<label for="password">Password</label>
					<input type="password" name="password" placeholder="Password">
					<input type="submit" value="Save">
				</form>
				<div class="profile-picture-container">
					<label>Profile Picture</label>
					<div class="profile-pic">
						<label class="-label" for="file">
							<span class="glyphicon glyphicon-camera"></span>
							<span>Change Image</span>
						</label>
						<input id="file" type="file" onchange="changeProfilePicture(event)"/>
						<img alt="Profile Picture of <?php echo $user->username ?>"
							 src="<?php echo $user->profile_picture ?? "https://gravatar.com/avatar/" . md5(strtolower(trim($user->email))) . "?s=200&d=mp" ?>"
							 class="profile-picture" width="200"/>
					</div>
				</div>
			</div>
		</div>
	</div>


<?php
	include_once "../scaffolding/footer.php";