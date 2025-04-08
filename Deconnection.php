<?php
require 'Connection_db.php';

$email = $_COOKIE['email'];
$token = $_COOKIE['token'];

if($token){

    //echo "1.0 deconnx";
    $pdo->exec("UPDATE users SET token = '' WHERE email = '$email' AND token = '$token' ");

    unset($_COOKIE['token']);
    setcookie('token', '', time() - 3600, '/'); // empty value and old timestamp

    unset($_COOKIE['email']);
    setcookie('email', '', time() - 3600, '/'); // empty value and old timestamp
    header("location: login.php");
    exit();

}else{
    //echo "2.0 deconnx";
    header("location: login.php");
}
?>