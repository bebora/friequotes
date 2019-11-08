<?php

require "../common.php";

if (isset($_GET['id'])) {
    try  {
        $connection = getdb();
        $stmt = $connection->prepare('SELECT * FROM entities WHERE id = :id');
        $id = $_GET['id'];
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        $name = $result['name'];
        $picpath = 'uploads/' .$result['propicpath'];
        $resultinfo = '<div class="userphoto"><img src="' . $picpath . '" alt="' . 'Foto profilo non esistente"></div>' .
            '<div class="userinfo" style="padding: 5px">Nome: ' . $name . '<br>Compleanno: ' . $result['birthday'] . '</div>';

        $sql = "SELECT  *
                FROM posts
                LEFT JOIN postusertags on posts.id = postusertags.postid
                WHERE postusertags.entityid = :id
                ORDER BY created DESC ";

        $statement = $connection->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $resultposts = $statement->fetchAll();

    } catch(PDOException $error) {
    echo $error->getMessage();
    }
}
else {
    $name = 'Errore';
}
?>
<?php
$pageTitle = escape($name) . " - Profilo";
$scripts = '<script src="scripts/upload.js" defer></script>';
include 'templates/header.php';
?>
    <h2>Profilo di <?php echo escape($name) ?></h2>
    <div class="infoandphoto">
        <?php echo $resultinfo ?>
    </div>
    <a href="usermedia.php?id=<?php echo $_GET['id'] ?>" style="padding-left: 5px">Altre foto di <?php echo escape($name) ?></a>
    <h3>Post in cui <?php echo escape($name) ?> è taggato</h3>
    <div class="postlist">
        <?php echo renderposts($resultposts, count($resultposts))?>
    </div>
    <form enctype="multipart/form-data" id="file-form" method="POST">
        <div>
            <h4>Carica una foto profilo</h4>
            <p id="progressdiv"><progress max="100" value="0" id="progress" style="display: none;"></progress></p>
            <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
            <input type="file" name="file-select" id="file-select">
            <button type="submit" id="upload-button">Carica</button><br><br>
        </div>
    </form>
    <script defer src="data:text/javascript, uploadmedia('userpropic'); "></script>
<?php require "templates/footer.php"; ?>