<?php
/*
 * Bookmark-Page (https://github.com/LeeO86/Bookmark-Page)
 *
 * Copyright 2020 Adrian Hilber
 * Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
 */

// Since PHP 8.1 mysqli throws exceptions by default; this app checks
// return values instead, so switch back to the old behaviour.
mysqli_report(MYSQLI_REPORT_OFF);

// Connection variables (overridable via environment)
$host = getenv('DB_HOST') ?: "db"; // MySQL host name eg. localhost
$user = getenv('DB_USER') ?: "bookmark"; // MySQL user. eg. root ( if your on localserver)
$password = getenv('DB_PASSWORD') ?: "bookpass"; // MySQL user password  (if password is not set for your root user then keep it empty )
$database = getenv('DB_NAME') ?: "bookmark-db"; // MySQL Database name

// Connect to MySQL Database
$con = @new mysqli($host, $user, $password, $database);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
$con->set_charset('utf8mb4');
?>
