<?php

require '../common.php';
require '../render.php';
check_token(LoginLevel::GUEST);
if (isset($_GET['id'])) {
    try  {
        $connection = get_db();
        $stmt = $connection->prepare('SELECT *
                                                FROM posts
                                                LEFT JOIN posthashtags on posts.id = posthashtags.postid
                                                WHERE posthashtags.tagid = :id');
        $id = $_GET['id'];
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt = $connection->prepare('SELECT name
                                                FROM tags
                                                WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $tagname = $stmt->fetch()["name"];
    } catch(PDOException $error) {
        echo $error->getMessage();
    }
}
?>
<?php
$pageTitle = '#' . $tagname;
include 'templates/header.php';
?>
    <h2>Post taggati con #<?php echo escape($tagname) ?></h2>
    <?php echo render_posts($result, count($result))?>

<?php require 'templates/footer.php'; ?>