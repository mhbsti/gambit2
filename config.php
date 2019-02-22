<?php
$board = ''; //trello id
$board_bd = 1;

$espera = 1;//segundos

$key = "";//
$token = "";
$server = "https://api.trello.com/1/";

$host = 'db';
$dbname = 'gambit';
$username = 'root';
$password = 'root';
$charset = 'latin1';
$collate = 'latin1_swedish_ci';
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => false,
    PDO::ATTR_EMULATE_PREPARES => true,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset COLLATE $collate"
];

