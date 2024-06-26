<?php

/**
 * Function to query information based on
 * a parameter: in this case, location.
 *
 */
require '../common.php';
require "../render.php";
check_token(LoginLevel::GUEST);


if (isset($_GET['query'])) {
    $mode = 2;
    try {
        $connection = get_db();
        if (isset($_GET['query'][0])) {
            // $string is greater than 0
            switch ($_GET['query'][0]):
                case "@":
                    // Searching user
                    $mode = 0;
                    $sql = "SELECT * 
                    FROM entities
                    WHERE name LIKE :name
                    ORDER BY name";
                    $name = '%'.substr($_GET['query'], 1).'%';
                    $statement = $connection->prepare($sql);
                    $statement->bindParam(':name', $name, PDO::PARAM_STR);
                    $statement->execute();
                    $result = $statement->fetchAll();
                    break;
                case "#":
                    // Searching tag!
                    $mode = 1;
                    $sql = "SELECT  *
                    FROM tags
                    WHERE name LIKE :name
                    ORDER BY name";

                    $name = '%'.substr($_GET['query'], 1).'%';
                    $statement = $connection->prepare($sql);
                    $statement->bindParam(':name', $name, PDO::PARAM_STR);
                    $statement->execute();
                    $result = $statement->fetchAll();
                    break;
                default:
                    // Searching posts
                    $mode = 2;
                    $query = '%'.$_GET['query'].'%';
                    if (isset($_GET['enabledesc']) && $_GET['enabledesc'] == 1) {
                        $sql = "SELECT  *
                        FROM posts
                        WHERE title LIKE :query OR description LIKE :query";
                    }
                    else {
                        $sql = "SELECT  *
                        FROM posts
                        WHERE title LIKE :query";
                    }
                    $statement = $connection->prepare($sql);
                    $statement->bindParam(':query', $query, PDO::PARAM_STR);
                    $statement->execute();
                    $result = $statement->fetchAll();
                    break;
            endswitch;
        }
        else $result = array();

    } catch (PDOException $error) {
        echo $sql . "<br>" . $error->getMessage();
    }
}
?>
<?php
$config = get_config();
$pageTitle = escape('Ricerca ' . $config->sitename);
include 'templates/header.php';
if (!isset($_GET['noheading'])) {
?>
    <h2>Trova utenti, post o collezioni di post</h2>
    <p>Testo normale: ricerca post; testo preceduto da @: ricerca utenti; testo preceduto da #: ricerca collezioni</p>
    <form method="get" id="search">
        <label for="query">Cosa cerchi?</label>
        <input type="text" id="query" name="query">
        <button type="submit" form="search">Cerca</button>
        <label for="enabledesc" style="word-wrap:break-word">
            <input id="enabledesc" name="enabledesc" type="checkbox" value="1" > Cerca anche nella descrizione dei post
        </label>
    </form>
<?php }
if (isset($_GET['query'])) {
    if ($result && count($result) > 0) {
        if (!isset($_GET['noheading'])) {?>
            <h2>Risultati</h2>
        <?php }
        if($mode == 0) {
            echo render_users_searchpage($result, count($result));
        }
        else if($mode == 1) {
            echo render_hashtags($result, count($result));?>
        <?php }
        else if($mode == 2) {
            echo render_posts($result, count($result));
        }?>
    <?php } else { ?>
        <blockquote>Nessun risultato per <?php echo escape($_GET['query']); ?>.</blockquote>
        <?php  switch($mode):
            case 2:
                echo 'Vuoi provare a cercare fra gli <a href="search.php?query=@' . escape($_GET['query']) . '">utenti</a> o gli <a href="search.php?query=%23' . escape($_GET['query']) . '">hashtag</a>?';
                break;
            case 1:
                echo 'Vuoi provare a cercare fra gli <a href="search.php?query=@' . escape(substr($_GET['query'], 1)) . '">utenti</a> o i <a href="search.php?query=' . escape(substr($_GET['query'], 1)) . '">post</a>?';
                break;
            case 0:
                echo 'Vuoi provare a cercare fra gli <a href="search.php?query=%23' . escape(substr($_GET['query'], 1)) . '">hashtag</a> o i <a href="search.php?query=' . escape(substr($_GET['query'], 1)) . '">post</a>?';
                break;
            default:
                break;
        endswitch;?>
    <?php }
} ?>

<?php require 'templates/footer.php'; ?>
