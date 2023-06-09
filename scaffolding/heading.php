<?php
	require_once "db/account.php";

	include_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

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
    <link rel="stylesheet" href="<?php echo $config->root_path ?>style.css">
    <title>GudPC</title>
</head>
<body>
<script defer>
    function logout() {
        fetch("<?php echo $config->root_path; ?>api/logout.php")
            .then(response => {
                if (response.ok) {
                    window.location.reload();
                } else {
                    throw new Error(response.statusText);
                }
            })
            .catch(error => {
                console.error(error);
                alert("An error occurred while logging out!")
            });
    }
</script>
<div class="navbar_outer" data-aos="zoom-in-down" data-aos-duration="600">
    <div class="navbar">
        <a href="/GudPC" class="name headline">GudPC</a>
        <div class="navbar_links">
            <a class="shop_button navbar_button"
               href="<?php echo $config->root_path ?>shop.php">
                <img alt="change locale" src="<?php echo $config->root_path ?>res/shop.svg">
            </a>
			<?php
				function fillCartBadge($user, $config)
				{
					if ($user == null) return;
					echo '
                    <a class="cart_button navbar_button icon-badge-group"
                         href="'.$config->root_path.'cart.php">
                        <div class="icon-badge-container">
                            <img alt="cart" src="'.$config->root_path.'res/cart.svg">';
					echo '<div class="icon-badge" id="cart_count">';
					require_once $_SERVER["DOCUMENT_ROOT"].$config->root_path."scaffolding/db/cart.php";
					$dbconn = pg_connect($config->db_connection_string);
					echo getCartCount($dbconn, $user->id);
					echo '</div>';
                    echo '
                        </div>
                    </a>';
				}

				fillCartBadge($user, $config);
			?>
            <div class="account_link" onclick="toggleDropdown()">
                <div id="dropdown">
                    <img alt="your profile picture"
                         class="account_button navbar_button profile-picture"
                         src="<?php echo $user ? $user->profile_picture?->getUrl() : $config->root_path.'res/unknown-person.svg' ?>"
                         style="width: 3em; height: 3em; border-radius: 69%">
                    <div class="dropdown_content">
						<?php
							if (isset($_SESSION["user"])) {
								echo "<a href=\"{$config->root_path}account/my-account.php\">My Account</a>";
                                echo "<a href=\"{$config->root_path}payment-history.php\">Payment history</a>";
								echo "<a onclick='logout()'>Logout</a>";
							} else {
								echo "<a href=\"{$config->root_path}account/login.php\">Login</a>";
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

        function updateCartCount(number) {
            let element = document.getElementById("cart_count");
            element.innerText = number;
            // force redraw
            element.classList.remove("pulse-animation")
            element.offsetWidth
            element.classList.add("pulse-animation")
        }
    </script>
</div>
