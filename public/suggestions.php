<?php
include '../common.php';
$db = get_db();
if (isset($_GET['query'])) {
    $connection = $db->prepare("SELECT name AS label, id AS value 
                                          FROM entities
                                          WHERE name LIKE :name");
    $connection->execute(array(":name" => '%' . $_GET['query'] . '%'));
    $result = $connection->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
}
else {
    $connection = $db->prepare("SELECT name AS label, id AS value 
                                          FROM entities
                                          LIMIT 15");
    $connection->execute();
    $result = $connection->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
}