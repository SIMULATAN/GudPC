<?php
	include("scaffolding/heading.php");
?>
<div class="panel header" data-aos="fade-up">
	<div class="panel_inner header_inner headline">
		<h1>GudPC</h1>
		<p>The worst computers for the highest price</p>
	</div>
</div>

<?php
	function create_panel($title, $description, $img) {
		echo "<div class=\"panel\" data-aos=\"fade-up\">";
		echo "<div class=\"panel_inner\">";
		// check if the image exists
		$img = "res/$img";
		if (file_exists("$img")) {
			echo "<img src=\"$img\"/>";
		}
		echo "<h1 class='headline'>$title</h1>";
		echo "<p>$description</p>";
		echo "</div>";
		echo "</div>";
	}
?>

<div class="full_height">
	<div id="about_panels">
		<div class="panel_fw">
			<?php
				create_panel("No deliveries", "We never deliver what we promised", "deliveries.svg");
				create_panel("Highest prices", "You'll get a car cheaper than our PCs", "money.svg");
				create_panel("Worst service", "We do not care about your issues", "service.svg");
			?>
		</div>
		<div class="panel_fw">
			<?php
				create_panel("0 day Money Back guarantee", "Haha get scammed", "debt.svg");
				create_panel("Customers hate us!", "They're all angry at us and would like to know our address", "angry.svg");
				create_panel("No experience at all", "We have zero clue what the hell we're doing", "time.svg");
			?>
		</div>
	</div>
</div>

<?php
	include("scaffolding/footer.php");
?>
