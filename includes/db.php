<?php
/**
 * Created by PhpStorm.
 * User: bclincy
 * Date: 7/14/16
 * Time: 8:25 PM
 */
$dsn = 'mysql:dbname=bonnier;host=127.0.0.1';
$user = 'bonnier';
$password = 'root';
try {
    $db = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
