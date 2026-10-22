<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

if(isset($_POST['json'])){
	include 'db-conn.php';
	$json = $_POST['json'];
	$array = json_decode($json);

	$stmt = $con->prepare('UPDATE `bookmarks` SET `sort` = ? WHERE `bookmarks`.`id` = ?');
	foreach ($array as $id => $sort) {
		$bmId = intval($id);
		$sortInt = intval($sort);
		$stmt->bind_param('ii', $sortInt, $bmId);
		if (!$stmt->execute()) {
			exit(mysqli_error($con));
		}
	}
	$stmt->close();

	http_response_code(200);
	echo("OK");

	mysqli_close($con);
}else{
	http_response_code(400);
	echo("HTTP-POST ERROR");
}

?>
