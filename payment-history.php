<?php
	include_once "scaffolding/heading.php";

	include_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

	if (!isset($_SESSION["user"])) {
		echo "<script>window.location.href = '$config->root_path/account/login.php';</script>";
		die();
	}

	$dbconn = pg_connect($config->db_connection_string);
?>

<link href="css/payment-history.css" rel="stylesheet">

<div class="full_height">
    <div id="history">
        <h1>Payment history</h1>
        <div id="history-entries">
            <table id="history-table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total price</th>
                </tr>
                </thead>
                <tbody>
				<?php
					$result = pg_query_params($dbconn, "SELECT * FROM checkout WHERE user_id = $1 ORDER BY date DESC", array($_SESSION["user"]->id));

					$totalPrice = 0;
					while ($row = pg_fetch_assoc($result)) {
						echo "<tr class='history_entry'>";
						echo "<th>${row['date']}</th>";
						echo "<th>Scammed</th>";
						$totalPrice += $row['total_price'];
						echo "<th>${row['total_price']}€</th>";
						echo "</tr>";
					}
				?>
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: space-between; width: 100%">
			<?php
				echo "<a class='button' style='margin-top: 1%; padding: 0.25% 2%' href='$config->root_path/api/download-payment-history.php'>Export as CSV</a>";
				echo "<p>Total money wasted: ${totalPrice}€</p>";
			?>
        </div>
    </div>
</div>
