<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

if(isset($_POST['title']) && isset($_POST['claim']) && isset($_POST['refresh']) && isset($_POST['favcolor']) && isset($_POST['background']) && isset($_POST['userCol'])){
	include 'db-conn.php';

	$values = array(
		'name' => $_POST['title'],
		'claim' => $_POST['claim'],
		'refresh' => $_POST['refresh'],
		'favcolor' => $_POST['favcolor'],
		'background' => $_POST['background'],
		'userCol' => $_POST['userCol'],
	);
	for ($i = 1; $i <= 8; $i++) {
		$values['nameU'.$i] = $_POST['nameU'.$i] ?? '';
		$values['hideU'.$i] = $_POST['hideU'.$i] ?? '0';
	}

	$stmt = $con->prepare('UPDATE `global` SET `value` = ? WHERE `global`.`key` = ?');
	foreach ($values as $key => $value) {
		$stmt->bind_param('ss', $value, $key);
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
