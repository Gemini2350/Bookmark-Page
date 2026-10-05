<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

header('Content-Type: application/json; charset=utf-8');

function fail($msg, $code = 400) {
	http_response_code($code);
	echo json_encode(array('error' => $msg));
	exit;
}

if (!isset($_POST['target']) || !isset($_POST['json'])) {
	fail('Missing target group or bookmark ids.');
}

$ids = json_decode($_POST['json']);
if (!is_array($ids) || count($ids) === 0) {
	fail('No bookmark ids given.');
}

include 'db-conn.php';

// resolve target group
$stmt = $con->prepare('SELECT `id` FROM `groups` WHERE `name` = ?');
$stmt->bind_param('s', $_POST['target']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$row) {
	fail('Target group not found.');
}
$targetId = intval($row['id']);

// append moved bookmarks after the target group's existing ones
$stmt = $con->prepare('SELECT COALESCE(MAX(`B`.`sort`), 0) AS maxsort FROM `bookmarks` `B` INNER JOIN `link-groups-bookmarks` `L` ON `B`.`id` = `L`.`bookmark-id` WHERE `L`.`group-id` = ?');
$stmt->bind_param('i', $targetId);
$stmt->execute();
$sort = intval($stmt->get_result()->fetch_assoc()['maxsort']);
$stmt->close();

$stmtLink = $con->prepare('UPDATE `link-groups-bookmarks` SET `group-id` = ? WHERE `bookmark-id` = ?');
$stmtSort = $con->prepare('UPDATE `bookmarks` SET `sort` = ? WHERE `id` = ?');

$moved = 0;
foreach ($ids as $id) {
	$bmId = intval($id);
	if ($bmId <= 0) continue;
	$stmtLink->bind_param('ii', $targetId, $bmId);
	if (!$stmtLink->execute()) {
		fail('DB error moving bookmark '.$bmId.': '.$con->error, 500);
	}
	if ($stmtLink->affected_rows > 0) {
		$sort++;
		$stmtSort->bind_param('ii', $sort, $bmId);
		$stmtSort->execute();
		$moved++;
	}
}
$stmtLink->close();
$stmtSort->close();

echo json_encode(array('ok' => true, 'moved' => $moved));

mysqli_close($con);
?>
