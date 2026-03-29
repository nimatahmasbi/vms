<?php
$host = 'localhost';
$db_name = 'sir_crm';
$username = 'sir_crm';
$password = 'XUvtzhXFN6AutStwuLzB';

try {
	$db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Connection error: " . $e->getMessage());
}