<?php
	include "scaffolding/heading.php";

	require_once "config/config.php";
	global $config;

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	if (!isset($_SESSION["user"])) {
		echo '<script>window.location.href = "account/login.php";</script>';
		die();
	}

	$userId = $_SESSION["user"]->id;

	$cart_empty_text = '
        <p class="empty_cart">Your cart is empty!</p>
        <div style="display: flex; justify-content: center; width: 100%">
            <a class="button cart_shop_button" style="margin-top: 1%" href="shop.php">Shop</a>
        </div>
    ';
	$checkout_text = '
        <div style="display: flex; justify-content: center; width: 100%">
            <a class="button cart_shop_button" style="margin-top: 0.5%" href="confirm-checkout.php">Checkout</a>
        </div>
    ';
?>
<link rel="stylesheet" href="css/cart.css">

<script>
    function showEmptyCartText() {
        // shitty ass JS can't handle newlines so we have to replace them (epic fail)
        document.getElementById("cart").innerHTML = '<?php echo str_replace("\n", "", $cart_empty_text); ?>';
    }

    function showCheckoutText() {
        document.getElementById("button-area").innerHTML = '<?php echo str_replace("\n", "", $checkout_text); ?>';
    }

    function removeProduct(productId) {
        fetch(`api/cart.php?action=remove&product_id=${productId}`)
            .then(response => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error(response.statusText);
                }
            })
            .then(data => {
                    updateCartCount(data);
                    document.getElementById("product-" + productId).remove();
                    if (data == 0) showEmptyCartText();
                    else showCheckoutText()
                }
            );
    }

    function updateQuantity(operation, productId) {
        let productElement = document.getElementById("product-" + productId);
        let quantity = productElement.getElementsByClassName("quantity-num")[0];
        let quantityNum = parseInt(quantity.innerText);
        if (operation === "+") {
            quantityNum++;
        } else if (operation === "-") {
            quantityNum--;
        }
        fetch(`api/cart.php?action=quantity&product_id=${productId}&quantity=${quantityNum}`)
            .then(response => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error(response.statusText);
                }
            })
            .then(data => {
                updateCartCount(data);
                if (quantityNum === 0) {
                    productElement.remove();
                } else {
                    quantity.innerText = quantityNum;
                    let price = productElement.getElementsByClassName("price")[0];
                    let priceNum = parseFloat(price.getAttribute("data-piece-price"));
                    let priceRoundedBecauseJSFloatingPointMathSucks = Math.round(priceNum * quantityNum * 100) / 100;
                    let pricePaddedToTwoDecimals = priceRoundedBecauseJSFloatingPointMathSucks.toFixed(2);
                    price.innerText = quantityNum + " x " + priceNum + "€ = " + pricePaddedToTwoDecimals + "€";
                }

                if (data == 0) showEmptyCartText();
                else showCheckoutText();
            });
    }
</script>

<div class="full_height">
    <div id="cart">
		<?php
			require_once "scaffolding/db/cart.php";
			$dbconn = pg_connect($config->db_connection_string);
			$cart_items = listCart($dbconn, $userId);
			pg_close($dbconn);
			if (count($cart_items) == 0) {
				echo $cart_empty_text;
			}
			foreach ($cart_items as $item) {
				echo "<div class='cart_entry' id='product-${item['id']}'>";
				echo "<div>";
				echo "<p>${item['name']}</p>";
				echo "</div>";
				echo "<div>";
				echo "<div class='change_quantity_buttons'>";
                $totalPrice = $item['quantity'] * $item['price'];
                echo "<p class='price' data-piece-price='${item['price']}'>${item['quantity']} x ${item['price']}€ = ${totalPrice}€</p>";
				echo "<p class='quantity' onclick='updateQuantity(\"-\", ${item['id']})'>-</p>";
				echo "<p class='quantity-num'>${item['quantity']}</p>";
				echo "<p class='quantity' onclick='updateQuantity(\"+\", ${item['id']})'>+</p>";
				echo "</div>";
				echo "<p class='remove_button' onclick='removeProduct(${item['id']})'>X</p>";
				echo "</div>";
				echo "</div>";
			}
		?>
        <div id="button-area"></div>
        <?php
            if (count($cart_items) > 0) {
                echo '<script>showCheckoutText();</script>';
            }
        ?>
    </div>
</div>

<?php
	include "scaffolding/footer.php";
?>
