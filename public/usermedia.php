<?php

require '../common.php';
require "../render.php";
check_token(LoginLevel::GUEST);
$can_delete = require_login(LoginLevel::MODERATOR) == LoginResult::OK;
$can_upload_media = require_login(LoginLevel::USER) == LoginResult::OK;
if (isset($_GET['id'])) {
    try  {
        $connection = get_db();
        $stmt = $connection->prepare('SELECT * FROM entities WHERE id = :id');
        $id = $_GET['id'];
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        $name = $result['name'];
        $sql = "SELECT  mediapath, mediaid
                FROM entitiesmedia
                WHERE entityid = :id
                ORDER BY created";
        $statement = $connection->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->execute();
        $resultmedia = $statement->fetchAll();
    } catch(PDOException $error) {
        echo $error->getMessage();
    }
}
?>
<?php
$pageTitle = escape($name . " - Media");
$scripts = '<script src="scripts/upload.js" defer></script>
            <script src="scripts/removemedia.js" defer></script>';
$extrastyle = '<link rel="stylesheet" href="css/usermedia.css">';

include 'templates/header.php';
?>
    <h2>Foto di <a href="userinfo.php?id=<?php echo $_GET['id']?>"><?php echo escape($name) ?></a></h2>
        <?php echo render_medias($resultmedia, count($resultmedia), '/uploads/usermedia/', $can_delete)?>
    <?php if ($can_upload_media) { ?>
        <form action="" enctype="multipart/form-data" id="file-form" method="POST">
            <div id="upup">
                <h4>Carica un'altra foto</h4>
                <p id="progressdiv"><progress max="100" value="0" id="progress" style="display: none;"></progress></p>
                <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
                <input type="file" name="file-select"  id="file-select">
                <button type="submit" id="upload-button">Carica</button>
            </div>
        </form>
        <script defer src="data:text/javascript, uploadmedia('usermedia'); "></script>
    <?php }
    if ($can_delete) { ?>
        <script defer src="data:text/javascript, addRemoveMediaListeners('usermedia'); "></script>
    <?php }
require 'templates/footer.php'; ?>
