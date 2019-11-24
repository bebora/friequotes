<?php
require '../common.php';
check_token(LoginLevel::GUEST);
if(isset($_COOKIE['token'])) {
    $db = get_db();
    $sql = 'DELETE
            FROM tokens
            WHERE token = :token';
    $deleteToken = $db->prepare($sql);
    $deleteToken->execute(array(':token' => $_COOKIE['token']));
    setcookie('token', '', time() - 3600);
}
header('Location: index.php');
