<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

if(isset($_POST['id'])){
	include 'db-conn.php';
	$id = intval($_POST['id']);

	$stmt = $con->prepare('DELETE FROM `bookmarks` WHERE `bookmarks`.`id` = ?');
	$stmt->bind_param('i', $id);
	if (!$stmt->execute()) {
		exit(mysqli_error($con));
	}
	$stmt->close();
	$stmt = $con->prepare('DELETE FROM `link-groups-bookmarks` WHERE `link-groups-bookmarks`.`bookmark-id` = ?');
	$stmt->bind_param('i', $id);
	if (!$stmt->execute()) {
		exit(mysqli_error($con));
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
