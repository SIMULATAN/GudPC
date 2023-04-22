<?php
	$size = 256;

	$username = $_GET["username"] ?? "?";

	$char = "";
	// Get the first two upper case characters
	$matches = [];
	preg_match_all('/[A-Z]/', $username, $matches);
	if (count($matches[0]) >= 2) {
		$char = strtoupper($matches[0][0] . $matches[0][1]);
	} else {
		// Use the first letter
		$char = strtoupper(substr($username, 0, 1));
	}

	// Set the background color based on the first character of the username
	$hash = md5($username);
	$bgColor = "#" . substr($hash, 0, 6);

	$textColor = "#FFFFFF";
	$font = "../res/NotoSans-Regular.ttf";

	// Create the image
	$image = imagecreatetruecolor($size, $size);

	// Fill the image with the background color
	$bg = imagecolorallocate($image,
		hexdec(substr($bgColor, 1, 2)),
		hexdec(substr($bgColor, 3, 2)),
		hexdec(substr($bgColor, 5, 2))
	);
	if (isset($_GET["username"])) {
		imagefill($image, 0, 0, $bg);
	}

	// Add the text
	$fontSize = $size / 2;
	$textbox = imagettfbbox($fontSize, 0, $font, $char);
	$textWidth = abs($textbox[4] - $textbox[0]);
	$textHeight = abs($textbox[5] - $textbox[1]);

	// if the text is wider than 80% of the image width, scale it down
	if ($textWidth > $size * 0.8) {
		$fontSize = $fontSize * ($size * 0.7 / $textWidth);
		$textbox = imagettfbbox($fontSize, 0, $font, $char);
		$textWidth = abs($textbox[4] - $textbox[0]);
		$textHeight = abs($textbox[5] - $textbox[1]);
	}

	$x = ($size - $textWidth) / 2 - $textbox[0];
	$x = floor($x); // Round down to the nearest integer
	$y = ($size - $textHeight) / 2 - $textbox[1] + $textHeight;
	$y = floor($y); // Round down to the nearest integer

	// Set the font color
	$color = imagecolorallocate($image,
		hexdec(substr($textColor, 1, 2)),
		hexdec(substr($textColor, 3, 2)),
		hexdec(substr($textColor, 5, 2))
	);

	// Add the text to the image
	imagettftext($image, $fontSize, 0, $x, $y, $color, $font, $char);

	// Output the image
	header("Content-Type: image/png");
	imagepng($image);
	imagedestroy($image);
