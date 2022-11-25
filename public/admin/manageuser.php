<?php

require '../../common.php';
require "../../render.php";
check_token(LoginLevel::ADMIN);

$userid = $_GET['userid'] ?? -1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        echo "Token CSRF mancante";
        die();
    }
    /**
     * 2 different requests to handle with different params:
     * -Change user permission level: userid, authlevel
     * -Reset user password: userid, resetpass
     */
    if (isset($_POST['authlevel'])) {
        $new_level = $_POST['authlevel'];
        if ($new_level < 0)
            $new_level = 0;
        if ($new_level > 3)
            $new_level = 3;
        $db = get_db();
        $update = array(
            ":authlevel" => $_POST['authlevel'],
            ":userid" => $_POST['userid']
        );
        $sql = 'UPDATE users
                SET auth_level = :authlevel
                WHERE userid = :userid';
        $stm = $db->prepare($sql);
        $stm->execute($update);
    } elseif (isset($_POST['generate_reset_token'])) {
        $new_token = gen_random_bytes();
        $db = get_db();
        $update = array(
            ":reset_token" => $new_token,
            ":userid" => $_POST['userid']
        );
        $sql = 'UPDATE users
                SET reset_token = :reset_token
                WHERE userid = :userid AND reset_token IS NULL';
        $stm = $db->prepare($sql);
        $stm->execute($update);
    }
}

$db = get_db();
$sql = 'SELECT *
        FROM users
        WHERE userid = :userid';
$stm = $db->prepare($sql);
$stm->execute(array(
    ':userid' => $userid,
));
$result = $stm->fetchAll();
if (count($result) < 1) {
    echo "No user with given id";
} else {
    $inspected_user = $result[0];
    $pageTitle = 'Gestione utente - ' . $inspected_user['username'];
    include '../templates/header.php';
    echo render_complete_user_dashboard($inspected_user);
}
include '../templates/footer.php';
?>
