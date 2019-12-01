<?php

require '../common.php';
require "../render.php";
check_token(LoginLevel::GUEST);
$can_delete = require_login(LoginLevel::MODERATOR) == LoginResult::OK;
$can_edit = require_login(LoginLevel::MODERATOR) == LoginResult::OK;
$can_upload_media = require_login(LoginLevel::USER) == LoginResult::OK;
if (isset($_GET['id'])) {
    try  {
        $connection = get_db();
        $stmt = $connection->prepare('SELECT * FROM posts WHERE id = :id');
        $id = $_GET['id'];
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $resultPost = $stmt->fetch();
        if ($resultPost == null) {
            header("Location: /index.php");
            echo "Non esiste alcun post con questo id";
            die();
        }
        $sql = "SELECT *
                FROM entities
                JOIN postusertags on entities.id = postusertags.entityid
                WHERE postid = :postid";
        $statement = $connection->prepare($sql);
        $statement->bindParam(':postid', $id, PDO::PARAM_INT);
        $statement->execute();
        $resultUsertags = $statement->fetchAll();

        $getHashtagsQuery = "SELECT *
                FROM tags
                JOIN posthashtags p on tags.id = p.tagid
                WHERE postid = :postid";
        $getHashtags = $connection->prepare($getHashtagsQuery);
        $getHashtags->bindParam(':postid', $id, PDO::PARAM_INT);
        $getHashtags->execute();
        $resultHashtags = $getHashtags->fetchAll();

        //Then get media for this post
        $sql = "SELECT  mediapath, mediaid
            FROM postsmedia
            WHERE postid = :id
            ORDER BY created";
        $statement = $connection->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->execute();
        $resultmedia = $statement->fetchAll();
        if (isset($_GET['json']) && $_GET['json'] == 'true') {
            $json = new ArrayObject();
            $json['title'] = $resultPost['title'];
            $json['description'] = $resultPost['description'];
            $json['created'] = $resultPost['created'];
            $json['lastedit'] = $resultPost['lastedit'];
            $json['tags'] = array_map(function ($item) {
                return new ArrayObject(array('tagid' => $item['tagid'], 'name' => $item['name']));
            }, $resultHashtags);
            $json['entities'] = array_map(function ($item) {
                return new ArrayObject(array('entityid' => $item['entityid'], 'name' => $item['name']));
            }, $resultUsertags);
            header('Content-Type: application/json');
            echo json_encode($json, JSON_PRETTY_PRINT);
            die();
        }
    } catch(PDOException $error) {
        echo $error->getMessage();
    }
}
if (isset($_POST['submit'])) {
    if ($can_delete) {
        header('Location: /index.php');
        try  {
            $connection = get_db();
            $connection->exec( 'PRAGMA foreign_keys = ON;' );
            $stmt = $connection->prepare('DELETE FROM posts WHERE id = :id');
            $id = $_GET['id'];
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch(PDOException $error) {
            echo $error->getMessage();
        }
        die();
    }
}
?>
<?php
$pageTitle = $resultPost['title'];
$scripts = '<script src="scripts/upload.js" defer></script>
            <script src="scripts/removemedia.js" defer></script>';
$extrastyle = '<link rel="stylesheet" type="text/css" href="css/usermedia.css">';
include 'templates/header.php';
?>
    <?php echo render_post($resultPost)?>
    <?php if (count($resultUsertags) > 0) {
        echo '<p>Utenti taggati:';
        foreach ($resultUsertags as $row) :
            echo '<span class="usertag"><a href="userinfo.php?id=' . $row['entityid'] . '">' . escape($row['name']) . '</a></span>';
        endforeach;
        echo '</p>';
    }
    if (count($resultHashtags) > 0) {
        echo render_hashtags($resultHashtags, count($resultHashtags), true, 'Hashtag: ');
    }
    if (count($resultmedia) > 0) {
        echo render_medias($resultmedia, count($resultmedia), '/uploads/postmedia/');
    }
    if ($can_upload_media) { ?>
        <form enctype="multipart/form-data" id="file-form" method="POST">
            <div>
                <h4>Carica una foto dell'evento</h4>
                <p id="progressdiv">
                    <progress max="100" value="0" id="progress" style="display: none;"></progress>
                </p>
                <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
                <input type="file" name="file-select" id="file-select">
                <button type="submit" id="upload-button">Carica</button>
                <br><br>
            </div>
        </form>

    <?php }
    if ($can_delete) {?>
        <form id="delete-form" method="POST" onsubmit="return confirm('Vuoi davvero cancellare il post?');">
            <button type="submit" id="delete-button" name="submit">Cancella post ❌</button>
        </form>
    <?php } ?>
    <?php if ($can_edit && isset($_GET['id'])) {?>
        <a style="margin-bottom: 300px;" href="newpost.php?postid=<?php echo $_GET['id'] ?>">Modifica post ✏️</a>
    <?php }?>

    <br><br>
    <script defer src="data:text/javascript, uploadmedia('postmedia'); "></script>
    <script defer src="data:text/javascript, addRemoveMediaListeners('postmedia'); "></script>
<?php require 'templates/footer.php'; ?>