<?php
/**
 * Open a connection via PDO to create a new database and table with structure.
 */
include 'common.php';
try {
    $connection = get_db();
    $sql = file_get_contents('data/init.sql');
    $connection->exec($sql);
    echo 'Database and table users created successfully.';
} catch(PDOException $error) {
    echo $error->getMessage();
}

/**
 * Create upload folders
 */
$folders = array(
    'usermedia' => 'public/uploads/usermedia',
    'profilepics' => 'public/uploads/profilepics',
    'thumbnails' => 'public/uploads/thumbs',
    'postmedia' => 'public/uploads/postmedia'
);
foreach ($folders as $key => $value) {
    if (!file_exists($value)) {
        mkdir($value, 0777, true);
        echo 'Adding ' . $key . ' folder';
    }
}

