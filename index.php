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
	function createPanel($title, $description, $img)
	{
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
				createPanel("No deliveries", "We never deliver what we promised", "deliveries.svg");
				createPanel("Highest prices", "You'll get a car cheaper than our PCs", "money.svg");
				createPanel("Worst service", "We do not care about your issues", "service.svg");
			?>
		</div>
		<div class="panel_fw">
			<?php
				createPanel("0 day Money Back guarantee", "Haha get scammed", "debt.svg");
				createPanel("Customers hate us!", "They're all angry at us and would like to know our address", "angry.svg");
				createPanel("No experience at all", "We have zero clue what the hell we're doing", "time.svg");
			?>
		</div>
	</div>
    <a class="button" href="shop.php">Shop</a>
</div>

<div class="full_height">
    <div style="width: 100%; display: flex; justify-content: center;">
        <div class="panel_wrapper">
            <div class="panel_wrapper_inner" id="about_us_panel" style="min-width: 60vw; max-width: 90vw; margin: 2% 0;">
                <h1>About us</h1>
                <p>We are GudPC and we have 0 years of Experience in the PC selling area.
                <p>Here at GudPC, we try to sell you the worst of Computers to</p>
                <p>maximize our Profit whilst keeping the cost for us low.</p>
                <p></p>
                <p>Please buy our stuff, my cat needs Food and I cba to do a proper Job.</p>
                <p>Because of the fact that we will scam you, our Computers are Always in stock!</p>
                <p>How cool is that! Now click the button below and buy pls</p>
            </div>
        </div>
    </div>
    <a class="button" style="margin-top: 1%" href="shop.php">Shop</a>
</div>

<?php
	include("scaffolding/footer.php");
?>
