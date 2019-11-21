<?php

/**
 * Use an HTML form to create a new entry in the
 * users table.
 *
 */

require '../common.php';
check_token(LoginLevel::USER);
if (isset($_POST['submit'])) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) die();

    try  {
        $connection = get_db();
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

        $statement = $connection->prepare($sql);
        $statement->execute($new_post);
        //$postid = $connection->lastInsertId(); not thread safe

        $sql = sprintf(
            "SELECT id FROM posts WHERE %s",
            implode(" and ", array_map(function($x) {return sprintf("%s = :%s", $x, $x);}, array_keys($new_post)))
        );
        $statement = $connection->prepare($sql);
        $statement->execute($new_post);
        $postid = $statement->fetch()["id"];

        $taggedentities = array();
        $taggedtags = array();
        foreach (explode(',', $_POST['users']) as $temp) {
            if ($temp == "") continue;
            $stmt = $connection->prepare('SELECT * FROM entities WHERE name like :name');
            $name = '%'.trim($temp).'%';
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if (count($result) > 0) {
                $id = $result[0]["id"];
                if (!in_array($id, $taggedentities)) {
                    array_push($taggedentities, $id);
                    $sql = "INSERT INTO postusertags(postid, entityid) values (:postid, :id)";
                    $stmt = $connection->prepare($sql);
                    $stmt->execute(array("postid" => $postid, "id" => $id));
                }
            }
        }
        //Get all input values separated by commas as an array
        foreach (explode(',', $_POST['tags']) as $temp) {
            if ($temp == "") continue;
            $name = ucfirst(strtolower(preg_replace('/\s*(#)*([^\s]*)\s*/', '$2', $temp)));  //Remove hash symbol and whitespaces, then capitalize first letter
            $stmt = $connection->prepare('INSERT OR IGNORE INTO tags(name) VALUES(:name)');
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            $stmt = $connection->prepare('SELECT * FROM tags WHERE name = :name');
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if (count($result) > 0) {
                $id = $result[0]["id"];
                if (!in_array($id, $taggedtags)) {
                    array_push($taggedtags, $id);
                    $sql = "INSERT INTO posthashtags(postid, tagid) values (:postid, :id)";
                    $stmt = $connection->prepare($sql);
                    $stmt->execute(array("postid" => $postid, "id" => $id));
                }
            }
        }
    } catch(PDOException $error) {
        echo $error->getMessage();
    }
}
?>
<?php
$pageTitle = "Nuovo post";
require "templates/header.php";?>

<?php if (isset($_POST['submit']) && $statement) :
    echo '<blockquote>Storia su "' . escape($_POST['title']) . '" aggiunta con successo. <a href="post.php?id=' . escape($postid) . '"> Puoi vederla e aggiungere foto cliccando qui 📖</a></blockquote>';
endif; ?>

<?php if (isset($_POST['submit']) && !$statement) : ?>
    <blockquote>"<?php echo escape($_POST['title']); ?>" non aggiunta.</blockquote>
<?php endif; ?>

<h2>Aggiungi una storia epica</h2>
<form method="post">
    <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
    <label for="title">Titolo</label>
    <input type="text" name="title" id="title">
    <br>
    <label for="description">Descrizione</label>
    <textarea rows="4" cols="50" name="description" id="description"></textarea>
    <label for="users">Personaggi coinvolti separati da virgole</label>
    <textarea rows="4" cols="50" name="users" id="users"></textarea>
    <label for="tags">Hashtag/contesto (separati da virgole se multipli)</label>
    <textarea rows="4" cols="50" name="tags" id="tags"></textarea>
    <input type="submit" name="submit" value="Invia">
</form>

<a href="index.php">Homepage</a>

<?php require 'templates/footer.php'; ?>