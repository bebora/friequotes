<?php

require "../common.php";
check_token(LoginLevel::GUEST);
if (!isset($_GET['start'])) {
    $start = 0;
}
else {
    $start = $_GET['start'];
}

try  {
    $connection = get_db();
    $stmt = $connection->prepare('SELECT *
                                            FROM posts
                                            ORDER BY id DESC 
                                            LIMIT 11
                                            OFFSET :start');
    $stmt->bindParam(':start', $start);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $nextpage = count($result) == 11;
    $pagelimit = min(count($result), 10);
} catch(PDOException $error) {
    echo $error->getMessage();
}

?>
<?php
$pageTitle = "Feed - Pagina " . (intval($start/10)+1);
include 'templates/header.php';
?>
    <h2>Feed Friequotes</h2>
    <div class="postlist">
        <?php echo renderposts($result, $pagelimit); ?>
    </div>
    <div class="arrows">
    <?php if ($start>0) {?>
        <a href="feed.php?start=<?php echo max(0, $start-10)?>" class="arrow" style="float: left">⬅️</a>
    <?php }?>
    <?php if ($nextpage) {?>
        <a href="feed.php?start=<?php echo isset($_GET['start']) ? $_GET['start']+10 : 10?>" class="arrow" style="float: right">➡️</a>
    <?php }?>
    </div>

<?php require "templates/footer.php"; ?>