<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

header('Content-Type: application/json; charset=utf-8');

if (($_POST['confirm'] ?? '') !== 'yes') {
	http_response_code(400);
	echo json_encode(array('error' => 'Missing confirmation.'));
	exit;
}

include 'db-conn.php';

$deleted = 0;
if ($result = mysqli_query($con, 'SELECT COUNT(*) AS c FROM `bookmarks`')) {
	$deleted = intval(mysqli_fetch_assoc($result)['c']);
	mysqli_free_result($result);
}

$queries = array(
	'DELETE FROM `link-groups-bookmarks`',
	'DELETE FROM `bookmarks`'
);
$groupsDeleted = false;
if (($_POST['groups'] ?? '') === 'true') {
	$queries[] = 'DELETE FROM `groups`';
	$groupsDeleted = true;
}

foreach ($queries as $query) {
	if (!mysqli_query($con, $query)) {
		http_response_code(500);
		echo json_encode(array('error' => mysqli_error($con)));
		mysqli_close($con);
		exit;
	}
}

echo json_encode(array('ok' => true, 'deleted' => $deleted, 'groupsDeleted' => $groupsDeleted));

mysqli_close($con);
?>
