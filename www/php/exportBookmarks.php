<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

include 'db-conn.php';

$export = array();
$export['type'] = 'bookmark-page-export';
$export['exported'] = date('c');

// global settings (for reference / backup)
$global = array();
if ($result = mysqli_query($con, 'SELECT * FROM `global`')) {
	while ($row = mysqli_fetch_assoc($result)) {
		$global[$row['key']] = $row['value'];
	}
	mysqli_free_result($result);
}
$export['version'] = $global['version'] ?? '';
$export['global'] = $global;

// groups with their bookmarks
$groups = array();
if (!$resultG = mysqli_query($con, 'SELECT * FROM `groups` ORDER BY `groups`.`sort` ASC')) {
	http_response_code(500);
	exit(mysqli_error($con));
}
$stmt = $con->prepare('SELECT `B`.* FROM `bookmarks` `B` INNER JOIN `link-groups-bookmarks` `L` ON `B`.`id` = `L`.`bookmark-id` WHERE `L`.`group-id` = ? ORDER BY `B`.`sort` ASC');
while ($groupFetch = mysqli_fetch_assoc($resultG)) {
	$group = array();
	$group['name'] = $groupFetch['name'];
	$group['remarks'] = $groupFetch['remarks'];
	$group['variable'] = $groupFetch['variable'] ?? '';
	$group['sort'] = intval($groupFetch['sort']);
	$group['bookmarks'] = array();
	$stmt->bind_param('i', $groupFetch['id']);
	$stmt->execute();
	$resultBM = $stmt->get_result();
	while ($bmFetch = mysqli_fetch_assoc($resultBM)) {
		$bm = array();
		$bm['name'] = $bmFetch['name'];
		$bm['link'] = $bmFetch['link'];
		$bm['favicon'] = $bmFetch['favicon'];
		$bm['remarks'] = $bmFetch['remarks'];
		$bm['sort'] = intval($bmFetch['sort']);
		for ($i = 1; $i <= 8; $i++) {
			$bm['user'.$i] = $bmFetch['user'.$i];
		}
		$group['bookmarks'][] = $bm;
	}
	$groups[] = $group;
}
mysqli_free_result($resultG);
$stmt->close();
$export['groups'] = $groups;

$filename = 'bookmark-page-export_'.date('Y-m-d_His').'.json';
header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');
echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

mysqli_close($con);
?>
