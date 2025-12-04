<?php
$host = "127.0.0.1";
$db = "breachtimes";
$user = "root";
$pass = "rootpass";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    try {
        $pdo = new PDO(
            "mysql:host=$host;charset=$charset",
            $user,
            $pass,
            $options,
        );
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
        $pdo->exec("USE `$db`");

        $schema = file_get_contents(__DIR__ . "/../database.sql");
        $pdo->exec($schema);
    } catch (\PDOException $e2) {
        throw new \PDOException($e2->getMessage(), (int) $e2->getCode());
    }
}
?>
