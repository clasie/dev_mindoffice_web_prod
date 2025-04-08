<?php 
error_reporting(E_ALL);
ini_set('display_errors','On');

/*
$host =  '';
$dbname = '';  
$user = ''; 
$password = '';
*/

require 'creds.php';

$pdo = null;
$error_msg = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>