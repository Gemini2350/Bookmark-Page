<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

if(isset($_POST['id']) && isset($_POST['name']) && isset($_POST['link']) && isset($_POST['favicon']) && isset($_POST['remarks']) && isset($_POST['group'])){
	include 'db-conn.php';
	$id = $_POST['id'];
	$sort = intval($_POST['sort'] ?? '1');
	$name = $_POST['name'];
	$link = $_POST['link'];
	$favicon = $_POST['favicon'];
	$remarks = $_POST['remarks'];
	$u = array();
	for ($i = 1; $i <= 8; $i++) {
		$u[$i] = $_POST['user'.$i] ?? '';
	}
	$group = $_POST['group'];

	$stmt = $con->prepare('SELECT `id` FROM `groups` WHERE `name` = ?');
	$stmt->bind_param('s', $group);
	if (!$stmt->execute()) {
		exit(mysqli_error($con));
	}
	$groupFetch = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if ($groupFetch) {
		$groupId = intval($groupFetch['id']);
		if($id == '-1'){
			$stmt = $con->prepare('INSERT INTO `bookmarks` (`sort`, `link`, `favicon`, `name`, `remarks`, `user1`, `user2`, `user3`, `user4`, `user5`, `user6`, `user7`, `user8`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$stmt->bind_param('issssssssssss', $sort, $link, $favicon, $name, $remarks, $u[1], $u[2], $u[3], $u[4], $u[5], $u[6], $u[7], $u[8]);
			if (!$stmt->execute()) {
				exit(mysqli_error($con));
			}
			$bmId = $con->insert_id;
			$stmt->close();
			$stmt = $con->prepare('INSERT INTO `link-groups-bookmarks` (`group-id`, `bookmark-id`) VALUES (?, ?)');
			$stmt->bind_param('ii', $groupId, $bmId);
			if (!$stmt->execute()) {
				exit(mysqli_error($con));
			}
			$stmt->close();
		}else{
			$bmId = intval($id);
			$stmt = $con->prepare('UPDATE `bookmarks` SET `link` = ?, `favicon` = ?, `name` = ?, `remarks` = ?, `user1` = ?, `user2` = ?, `user3` = ?, `user4` = ?, `user5` = ?, `user6` = ?, `user7` = ?, `user8` = ? WHERE `bookmarks`.`id` = ?');
			$stmt->bind_param('ssssssssssssi', $link, $favicon, $name, $remarks, $u[1], $u[2], $u[3], $u[4], $u[5], $u[6], $u[7], $u[8], $bmId);
			if (!$stmt->execute()) {
				exit(mysqli_error($con));
			}
			$stmt->close();
			$stmt = $con->prepare('UPDATE `link-groups-bookmarks` SET `group-id` = ? WHERE `link-groups-bookmarks`.`bookmark-id` = ?');
			$stmt->bind_param('ii', $groupId, $bmId);
			if (!$stmt->execute()) {
				exit(mysqli_error($con));
			}
			$stmt->close();
		}
	}

	http_response_code(200);
	echo("OK");

	mysqli_close($con);
}else{
	http_response_code(400);
	echo("HTTP-POST ERROR");
}

?>
