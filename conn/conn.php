<?php
	$user = 'root';
	$pwd = '';
	$host = 'localhost';
	$db = 'cms';
	$conn = mysqli_connect($host, $user, $pwd, $db);
	mysqli_query($conn, 'SET CHARACTER SET utf8');
	mysqli_query($conn, 'SET SESSION COLLATION_CONNECTION="utf_general_ci"');
?>