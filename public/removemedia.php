<?php

require '../common.php';
check_token(LoginLevel::MODERATOR);
if (isset($_POST['media_type']) && isset($_POST['mediaid'])) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) die();
    $mediaid = $_POST['mediaid'];
    $media_type = $_POST['media_type'];
    $allowed_types = array(
            'usermedia' => 'entitiesmedia',
            'postmedia' => 'postsmedia'
    );
    if (array_key_exists($media_type, $allowed_types)) {
        try {
            $db = get_db();
            $remove_query = 'DELETE 
                            FROM ' . $allowed_types[$media_type] .
                            ' WHERE mediaid = :mediaid';
            $remove_stm = $db->prepare($remove_query);
            $remove_stm->bindValue(':mediaid', $mediaid, PDO::PARAM_INT);
            $remove_stm->execute();
            ;
        }
        catch(PDOException $error) {
            echo $error->getMessage();
        }
    }
    else {
        echo "Impossibile rimuovere l'elemento specificato";
    }
}
?>
