<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

$favcolors = '../img/favcolors';
$icons = '../img/icons';
$scanned_favcolors = array_values(array_diff(scandir($favcolors), array('..', '.')));
if(isset($_POST['color'])){
    // only files that actually exist in the favcolors directory may be copied
    $file = basename($_POST['color']);
    if (!in_array($file, $scanned_favcolors, true)) {
        http_response_code(400);
        echo("Invalid favicon");
        exit;
    }
    $src = $favcolors . '/' . $file;
    copy($src, '../favicon.ico');

    http_response_code(200);
	echo("OK");
}else{
	$scanned_icons = array_values(array_diff(scandir($icons), array('..', '.')));
	$out = array('favcolors' => $scanned_favcolors, 'icons' => $scanned_icons);
    header('Content-Type: application/json');
    echo json_encode($out);
}

?>
