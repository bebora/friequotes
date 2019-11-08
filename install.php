<?php
/**
 * Open a connection via PDO to create a new database and table with structure.
 */
include 'common.php';
try {
    $connection = getdb();
    $sql = file_get_contents("data/init.sql");
    $connection->exec($sql);
    echo "Database and table users created successfully.";
} catch(PDOException $error) {
    echo $error->getMessage();
}
