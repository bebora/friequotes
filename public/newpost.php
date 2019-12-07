<?php

/**
 * Use an HTML form to create a new entry in the
 * users table or to edit an existing one.
 *
 */

require '../common.php';
check_token(LoginLevel::USER);
$can_edit = require_login(LoginLevel::MODERATOR) == LoginResult::OK;
if (isset($_GET['postid']))
    $postid = $_GET['postid'];
if (isset($_POST['submit'])) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) die();
    try {
        $db = get_db();
        if (isset($postid)) {
            //Only mods can edit posts
            check_token(LoginLevel::MODERATOR);
            //$db->exec( 'PRAGMA foreign_keys = ON;' );
            $updatePost = $db->prepare(
                    "UPDATE posts
                    SET title = :title, description = :description, lastedit = :lastedit
                    WHERE id = :postid ");
            $updatePost->execute(array(
                ':postid' => $postid,
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':lastedit' => date('c')
            ));
            //Delete hashtags and usertags, they will be regenerated
            $deleteHashtags = $db->prepare(
                    "DELETE
                    FROM posthashtags
                    WHERE postid = :postid"
            );
            $deleteHashtags->execute(array(':postid' => $postid));
            $deleteUsertags = $db->prepare(
                "DELETE
                    FROM postusertags
                    WHERE postid = :postid"
            );
            $deleteUsertags->execute(array(':postid' => $postid));
            $updated = true;
        }
        else {
            $new_post = array(
                "title" => $_POST['title'],
                "description" => $_POST['description'],
                "created" => date("c"),
                "lastedit" => date("c"),
            );

            $sql = sprintf(
                "INSERT INTO %s (%s) values (%s)",
                "posts",
                implode(", ", array_keys($new_post)),
                ":" . implode(", :", array_keys($new_post))
            );

            $statement = $db->prepare($sql);
            $statement->execute($new_post);
            //$postid = $connection->lastInsertId(); not thread safe

            $sql = sprintf(
                "SELECT id FROM posts WHERE %s",
                implode(" and ", array_map(function ($x) {
                    return sprintf("%s = :%s", $x, $x);
                }, array_keys($new_post)))
            );
            $statement = $db->prepare($sql);
            $statement->execute($new_post);
            $postid = $statement->fetch()["id"];
            if ($postid != null)
                $created = true;
        }
        // In every case, add entities and tags to the post as if they are new
        $taggedentities = array();
        $taggedtags = array();
        $stmt = $db->prepare('SELECT * FROM entities WHERE id = :id');
        foreach (explode(',', $_POST['taggedEnts']) as $temp) {
            if (trim($temp) == '') continue;
            $id = intval(trim($temp));
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result != null) {
                if (!in_array($id, $taggedentities)) {
                    array_push($taggedentities, $id);
                    $sql = 'INSERT INTO postusertags(postid, entityid) values (:postid, :id)';
                    $stmt = $db->prepare($sql);
                    $stmt->execute(array('postid' => $postid, 'id' => $id));
                }
            }
        }
        //Get all input values separated by commas as an array
        $insertHashtag = $db->prepare('INSERT OR IGNORE INTO tags(name) VALUES(:name)');
        $selectTag = $db->prepare('SELECT * FROM tags WHERE name = :name');
        $insertPostHashtag = $db->prepare('INSERT INTO posthashtags(postid, tagid) values (:postid, :id)');
        foreach (explode(',', $_POST['tags']) as $temp) {
            if ($temp == "") continue;
            $name = ucfirst(strtolower(preg_replace('/\s*(#)*([^\s]*)\s*/', '$2', $temp)));  //Remove hash symbol and whitespaces, then capitalize first letter
            $insertHashtag->bindParam(':name', $name);
            $insertHashtag->execute();
            $selectTag->bindParam(':name', $name);
            $selectTag->execute();
            $result = $selectTag->fetch();
            if ($result != null) {
                $id = $result['id'];
                if (!in_array($id, $taggedtags)) {
                    array_push($taggedtags, $id);
                    $insertPostHashtag->execute(array('postid' => $postid, 'id' => $id));
                }
            }
        }
    }
    catch(PDOException $error) {
        echo $error->getMessage();
    }
}
?>
<?php
$pageTitle = 'Nuovo post';
$scripts = '<script src="scripts/autocomplete.min.js" defer></script>
            <script src="scripts/postsuggestions.js" defer></script>';
$extrastyle = '<link rel="stylesheet" type="text/css" href="css/autocomplete.min.css">';
require "templates/header.php";?>

<?php if (isset($_POST['submit']) && isset($created)) :
    echo '<blockquote>Storia su "' . escape($_POST['title']) . '" aggiunta con successo. <a href="post.php?id=' . escape($postid) . '"> Puoi vederla e aggiungere foto cliccando qui 📖</a></blockquote>';

elseif (isset($_POST['submit']) && isset($updated)) :
    echo '<blockquote>Storia su "' . escape($_POST['title']) . '" aggiornata con successo. <a href="post.php?id=' . escape($postid) . '"> Puoi vederla e aggiungere foto cliccando qui 📖</a></blockquote>';

elseif (isset($_POST['submit']) && !isset($statement)) : ?>
    <blockquote>"<?php echo escape($_POST['title']); ?>" non aggiunta.</blockquote>
<?php endif; ?>

<h2>Aggiungi una storia epica</h2>
<form method="post">
    <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
    <label for="title">Titolo</label>
    <textarea rows="1" cols="50" name="title" id="title"></textarea>
    <br>
    <label for="description">Descrizione</label>
    <textarea rows="4" cols="50" name="description" id="description"></textarea>
    <label for="tags">Hashtag/contesto (separati da virgole se multipli)</label>
    <textarea rows="4" cols="50" name="tags" id="tags"></textarea>
    <label for="autocompInput">Inserisci uno o più personaggi taggati</label>
    <input id="autocompInput" type="text" />
    <div id="entdiv" class="entdiv"><?php if(isset($entdiv)) echo $entdiv;?></div>
    <input id="hiddenEnts" name="taggedEnts" type="hidden">
    <br>
    <input type="submit" name="submit" value="Invia">
</form>

<?php
if (isset($can_edit)) { ?>
    <script src="scripts/fillpost.js" defer></script>
    <script defer src="data:text/javascript, fillpost(<?php echo escape($postid)?>) "></script>
<?php }
require 'templates/footer.php'; ?>