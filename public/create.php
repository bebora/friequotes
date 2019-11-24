<?php

require '../common.php';
check_token(LoginLevel::MODERATOR);
if (isset($_POST['submit'])) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf']))
        die();
    if (!isset($_POST['name']) || !isset($_POST['bday']) || trim($_POST['name']) == '' || trim($_POST['bday']) == '') {
        $error = 'Inserisci sia nome sia compleanno!';
    }
    else {
        try  {
            $connection = get_db();
            $new_user = array(':name' => $_POST['name'], ':bday' => $_POST['bday']);
            //Check if someone with the same name already exists
            $checkExist = $connection->prepare('SELECT EXISTS(SELECT * FROM entities where name = :name) as exist');
            $checkExist->execute(array(':name' => $_POST['name']));
            $exist = $checkExist->fetch();
            if ($exist['exist'] == '1') {
                $error = 'Esiste già qualcuno che si chiama esattamente così purtroppo';
            }
            else {
                $sql = 'INSERT
                        INTO entities (name, birthday) 
                        VALUES (:name, :bday)';
                $statement = $connection->prepare($sql);
                $statement->execute($new_user);

                $getInserted = 'SELECT id
                                FROM entities
                                WHERE name = :name';
                $stm = $connection->prepare($getInserted);
                $stm->execute(array(':name' => $_POST['name']));
                $inserted = $stm->fetch();
                $insertAliases = $connection->prepare('INSERT INTO entityaliases (entityid, alias) VALUES (:entityid, :alias)');
                foreach (explode(',', $_POST['aliases']) as $temp) {
                    if (trim($temp) == '') {
                        continue;
                    }
                    else {
                        $insertAliases->execute(array(':alias' => trim($temp), ':entityid' => $inserted['id']));
                    }
                }
            }
        } catch(PDOException $error) {
            echo $error->getMessage();
        }
    }
}
?>
<?php
$pageTitle = 'Aggiungi personaggio';
include 'templates/header.php';
?>

  <?php if (isset($_POST['submit']) && $statement) : ?>
    <blockquote><?php echo escape($_POST['name']); ?> aggiunto con successo.</blockquote>
  <?php endif; ?>

<?php if (isset($_POST['submit']) && !$statement) : ?>
    <blockquote><?php echo escape($_POST['name']); ?> non aggiunto.</blockquote>
<?php endif; ?>

  <h2>Aggiungi un personaggio mistico</h2>
  <?php if (isset($error)) echo sprintf('<p style="color: blue;">%s</p>', escape($error)) ?>
  <form method="post">
      <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
      <label for="name">Nome</label>
      <input type="text" name="name" id="name">
      <label for="bday">Compleanno</label>
      <input type="date" name="bday">
      <label for="aliases">Ci sono altri nomi o abbreviazioni con cui viene chiamato questo personaggio? Inseriscili separandoli con una virgola</label>
      <textarea rows="4" cols="50" name="aliases" id="aliases"></textarea>
      <input type="submit" name="submit" value="Submit">
  </form>

  <a href="index.php">Back to home</a>

<?php require 'templates/footer.php'; ?>
