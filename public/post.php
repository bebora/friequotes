<?php

require "../common.php";
check_token(LoginLevel::GUEST);
$can_delete = require_login(LoginLevel::MODERATOR) == LoginResult::OK;
if (isset($_GET['id'])) {
    try  {
        $connection = get_db();
        $stmt = $connection->prepare('SELECT * FROM posts WHERE id = :id');
        $id = $_GET['id'];
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result == null) {
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
        $resultusers = $statement->fetchAll();
    } catch(PDOException $error) {
        echo $error->getMessage();
    }
}
if (isset($_POST['submit'])) {
    if ($can_delete) {
        header("Location: /index.php");
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
$pageTitle = $result["title"];
$scripts = '<script src="scripts/upload.js" defer></script>';
include 'templates/header.php';
?>
    <?php echo renderpost($result)?>
    <?php
        if (count($resultusers) > 0) {
            echo "<p>Utenti taggati:";
            foreach ($resultusers as $row) :
                echo '<span class="usertag"><a href="userinfo.php?id=' . $row['entityid'] . '">' . escape($row['name']) . '</a></span>';
            endforeach;
            echo '</p>';
        }
    ?>
    <form enctype="multipart/form-data" id="file-form" method="POST">
        <div>
            <h4>Carica una foto dell'evento</h4>
            <p id="progressdiv"><progress max="100" value="0" id="progress" style="display: none;"></progress></p>
            <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
            <input type="file" name="file-select"  id="file-select">
            <button type="submit" id="upload-button">Carica</button><br><br>
        </div>
    </form>
    <?php if ($can_delete) {
        ?>
            <form id="delete-form" method="POST" onsubmit="return confirm('Vuoi davvero cancellare il post?');">
                <button type="submit" id="delete-button" name="submit">Cancella post ❌</button>
            </form>
        <?php
    }?>
    <script defer src="data:text/javascript, uploadmedia('postmedia'); "></script>
<?php require "templates/footer.php"; ?>