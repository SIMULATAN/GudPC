<?php
	function goDie($message = "", $code = 500)
	{
		if ($message == "") {
			$message = "HTTP $code";
		}
		http_response_code($code);
		echo $message;
		die();
	}

	require_once "../scaffolding/db/account.php";

	if ($_SERVER["REQUEST_METHOD"] != "POST") {
		goDie("405 Method Not Allowed", 405);
	}

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	include_once $_SERVER["DOCUMENT_ROOT"] . "/GudPC/config/config.php";
	global $config;

	include_once "../scaffolding/db/account.php";

	$user = $_SESSION["user"] ?? null;
	if (!isset($user)) {
		goDie("401 Unauthorized", 401);
	}
	// add time to fix caching of existing profile pictures
	$file_id = md5($user->id) . "_" . time();

	$target_dir = "../uploads/profile/";
	$target_file = $target_dir . $file_id . ".jpg";

	$check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
	if (!$check) {
		goDie("File is not an image.", 400);
	}

	if ($_FILES["profile_picture"]["size"] > 8 * 1024 * 1024) {
		goDie("Sorry, your file is too large.", 400);
	}

	$imageFileType = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
	if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
		goDie("Sorry, only JPG, JPEG, PNG & GIF files are allowed. (file is $imageFileType)", 400);
	}

	// Generate thumbnail
	// Get the uploaded image
	$uploaded_image = $_FILES["profile_picture"]["tmp_name"];

	// Create a new image from the uploaded file
	$image = imagecreatefromstring(file_get_contents($uploaded_image));

	// Get the dimensions of the original image
	$width = imagesx($image);
	$height = imagesy($image);

	// Calculate the aspect ratios of the original and desired images
	$aspect_ratio_orig = $width / $height;
	$aspect_ratio_desired = 1; // 1:1 aspect ratio

	// Calculate the crop dimensions to maintain the aspect ratio
	if ($aspect_ratio_orig > $aspect_ratio_desired) {
		// Crop the width of the original image to match the desired aspect ratio
		$new_width = $height * $aspect_ratio_desired;
		$new_height = $height;
		$crop_x = ($width - $new_width) / 2;
		$crop_x = floor($crop_x); // Round down to the nearest integer
		$crop_y = 0;
	} else {
		// Crop the height of the original image to match the desired aspect ratio
		$new_width = $width;
		$new_height = $width / $aspect_ratio_desired;
		$crop_x = 0;
		$crop_y = ($height - $new_height) / 2;
		$crop_y = floor($crop_y); // Round down to the nearest integer
	}

	// Create a new, blank image to hold the resized and cropped version
	$new_image = imagecreatetruecolor(256, 256);

	// Crop the original image to the desired aspect ratio and size
	imagecopyresampled($new_image, $image, 0, 0, $crop_x, $crop_y, 256, 256, $new_width, $new_height);

	$image_name = $target_file; // This generates a unique filename based on the current time
	$image_path = $image_name; // This is the path where the image will be saved
	// Save the resized and cropped image as a JPEG to the specified directory
	if (!@imagejpeg($new_image, $image_path)) {
		goDie("Failed to save image.", 500);
	}

	if ($user->profile_picture == null) {
		$user->profile_picture = new UserProfilePicture($user, ProfilePictureType::UPLOAD->name . "|" . $file_id);
	} else {
		$user->profile_picture->type = ProfilePictureType::UPLOAD;
		$user->profile_picture->data = new UserProfilePictureData($file_id);
	}
	updateUser($user);

	echo $config->root_path . "uploads/profile/" . $file_id . ".jpg";
