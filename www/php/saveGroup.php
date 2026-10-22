<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

if(isset($_POST['old']) && isset($_POST['new']) && isset($_POST['json'])){
	include 'db-conn.php';
	$old = $_POST['old'];
	$new = $_POST['new'];
	$sort = intval($_POST['sort'] ?? '0');
	$remark = $_POST['remark'] ?? '';
	$variable = $_POST['variable'] ?? '';
	$json = $_POST['json'];
	$array = json_decode($json);

	$stmt = $con->prepare('UPDATE `groups` SET `sort` = ? WHERE `groups`.`id` = ?');
	foreach ($array as $group => $jsort) {
		$groupId = intval($group);
		$jsortInt = intval($jsort);
		$stmt->bind_param('ii', $jsortInt, $groupId);
		if (!$stmt->execute()) {
			exit(mysqli_error($con));
		}
	}
	$stmt->close();

	if($new != '-1'){
		if($old == '-1'){
			$stmt = $con->prepare('INSERT INTO `groups` (`sort`, `name`, `remarks`, `variable`) VALUES (?, ?, ?, ?)');
			$stmt->bind_param('isss', $sort, $new, $remark, $variable);
		}else{
			$oldId = intval($old);
			$stmt = $con->prepare('UPDATE `groups` SET `name` = ?, `remarks` = ?, `variable` = ? WHERE `groups`.`id` = ?');
			$stmt->bind_param('sssi', $new, $remark, $variable, $oldId);
		}
		if (!$stmt->execute()) {
			exit(mysqli_error($con));
		}
		$stmt->close();
	}

	http_response_code(200);
	echo("OK");

	mysqli_close($con);
}else{
	http_response_code(400);
	echo("HTTP-POST ERROR");
}

?>
