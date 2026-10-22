<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

if(isset($_POST['groupname'])){
	include 'db-conn.php';
	$name = $_POST['groupname'];

	$stmt = $con->prepare('SELECT COUNT(*) AS cnt FROM `link-groups-bookmarks` `L` INNER JOIN `groups` `G` ON `L`.`group-id` = `G`.`id` WHERE `G`.name = ?');
	$stmt->bind_param('s', $name);
	if (!$stmt->execute()) {
		exit(mysqli_error($con));
	}
	$fetch = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if ($fetch['cnt'] == 0){
		$stmt = $con->prepare('DELETE FROM `groups` WHERE `groups`.`name` = ?');
	}else{
		$stmt = $con->prepare('DELETE `B`, `G`, `L` FROM `bookmarks` `B` INNER JOIN `link-groups-bookmarks` `L` ON `B`.`id` = `L`.`bookmark-id` INNER JOIN `groups` `G` ON `L`.`group-id` = `G`.`id` WHERE `G`.name = ?');
	}
	$stmt->bind_param('s', $name);
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
