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

// accept either a file upload ("file") or a raw JSON string ("json")
$json = '';
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
	$json = file_get_contents($_FILES['file']['tmp_name']);
} elseif (isset($_POST['json'])) {
	$json = $_POST['json'];
} else {
	fail('No import data. Send a JSON file as "file" or a JSON string as "json".');
}

$import = json_decode($json, true);
if (!is_array($import)) {
	fail('Invalid JSON: '.json_last_error_msg());
}
if (($import['type'] ?? '') !== 'bookmark-page-export' || !isset($import['groups']) || !is_array($import['groups'])) {
	fail('Not a valid Bookmark-Page export file.');
}

include 'db-conn.php';

// existing groups: name -> id, and existing links (the app forbids duplicate links)
$groupIds = array();
$groupMaxSort = 0;
if ($result = mysqli_query($con, 'SELECT `id`, `name`, `sort` FROM `groups`')) {
	while ($row = mysqli_fetch_assoc($result)) {
		$groupIds[$row['name']] = intval($row['id']);
		$groupMaxSort = max($groupMaxSort, intval($row['sort']));
	}
	mysqli_free_result($result);
}
// existing links per group (the same link may exist in different groups,
// e.g. templates with {var} placeholders resolved per group)
$existingLinks = array();
if ($result = mysqli_query($con, 'SELECT `L`.`group-id` AS gid, `B`.`link` FROM `bookmarks` `B` INNER JOIN `link-groups-bookmarks` `L` ON `B`.`id` = `L`.`bookmark-id`')) {
	while ($row = mysqli_fetch_assoc($result)) {
		$existingLinks[$row['gid'].'|'.$row['link']] = true;
	}
	mysqli_free_result($result);
}

$stmtGroup = $con->prepare('INSERT INTO `groups` (`sort`, `name`, `remarks`, `variable`) VALUES (?, ?, ?, ?)');
$stmtBM = $con->prepare('INSERT INTO `bookmarks` (`sort`, `link`, `favicon`, `name`, `remarks`, `user1`, `user2`, `user3`, `user4`, `user5`, `user6`, `user7`, `user8`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmtLink = $con->prepare('INSERT INTO `link-groups-bookmarks` (`group-id`, `bookmark-id`) VALUES (?, ?)');
$stmtMaxSort = $con->prepare('SELECT COALESCE(MAX(`B`.`sort`), 0) AS maxsort FROM `bookmarks` `B` INNER JOIN `link-groups-bookmarks` `L` ON `B`.`id` = `L`.`bookmark-id` WHERE `L`.`group-id` = ?');

$groupsCreated = 0;
$imported = 0;
$skipped = 0;

foreach ($import['groups'] as $group) {
	$gName = trim(strval($group['name'] ?? ''));
	if ($gName === '' || $gName === '-1' || !isset($group['bookmarks']) || !is_array($group['bookmarks'])) {
		continue;
	}
	if (isset($groupIds[$gName])) {
		$gid = $groupIds[$gName];
	} else {
		$groupMaxSort++;
		$gRemarks = strval($group['remarks'] ?? '');
		$gVariable = strval($group['variable'] ?? '');
		$stmtGroup->bind_param('isss', $groupMaxSort, $gName, $gRemarks, $gVariable);
		if (!$stmtGroup->execute()) {
			fail('DB error creating group "'.$gName.'": '.$con->error, 500);
		}
		$gid = $con->insert_id;
		$groupIds[$gName] = $gid;
		$groupsCreated++;
	}
	// append imported bookmarks after the group's existing ones
	$stmtMaxSort->bind_param('i', $gid);
	$stmtMaxSort->execute();
	$bmSort = intval($stmtMaxSort->get_result()->fetch_assoc()['maxsort']);

	foreach ($group['bookmarks'] as $bm) {
		$link = trim(strval($bm['link'] ?? ''));
		$bName = strval($bm['name'] ?? '');
		if ($link === '' || $bName === '') {
			$skipped++;
			continue;
		}
		if (isset($existingLinks[$gid.'|'.$link])) {
			$skipped++;
			continue;
		}
		$bmSort++;
		$favicon = strval($bm['favicon'] ?? 'img/icons/bookmark.svg');
		$bRemarks = strval($bm['remarks'] ?? '');
		$u = array();
		for ($i = 1; $i <= 8; $i++) {
			$u[$i] = strval($bm['user'.$i] ?? '');
		}
		$stmtBM->bind_param('issssssssssss', $bmSort, $link, $favicon, $bName, $bRemarks, $u[1], $u[2], $u[3], $u[4], $u[5], $u[6], $u[7], $u[8]);
		if (!$stmtBM->execute()) {
			fail('DB error importing bookmark "'.$bName.'": '.$con->error, 500);
		}
		$bmId = $con->insert_id;
		$stmtLink->bind_param('ii', $gid, $bmId);
		if (!$stmtLink->execute()) {
			fail('DB error linking bookmark "'.$bName.'": '.$con->error, 500);
		}
		$existingLinks[$gid.'|'.$link] = true;
		$imported++;
	}
}

$stmtGroup->close();
$stmtBM->close();
$stmtLink->close();
$stmtMaxSort->close();

echo json_encode(array(
	'ok' => true,
	'groupsCreated' => $groupsCreated,
	'imported' => $imported,
	'skipped' => $skipped
));

mysqli_close($con);
?>
