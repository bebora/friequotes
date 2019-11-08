<?php

require "../common.php";

if (isset($_POST['submit'])) {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) die();

  try  {
    $connection = getdb();
    $new_user = array("name" => $_POST['name']);
    $sql = sprintf(
      "INSERT INTO %s (%s) values (%s)",
      "entities",
      implode(", ", array_keys($new_user)),
      ":" . implode(", :", array_keys($new_user))
    );
    $statement = $connection->prepare($sql);
    $statement->execute($new_user);
  } catch(PDOException $error) {
      echo $error->getMessage();
  }
}
?>
<?php
$pageTitle = 'Create';
include 'templates/header.php';
?>

  <?php if (isset($_POST['submit']) && $statement) : ?>
    <blockquote><?php echo escape($_POST['name']); ?> successfully added.</blockquote>
  <?php endif; ?>

<?php if (isset($_POST['submit']) && !$statement) : ?>
    <blockquote><?php echo escape($_POST['name']); ?> not added.</blockquote>
<?php endif; ?>

  <h2>Aggiungi un personaggio mistico</h2>

  <form method="post">
      <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
      <label for="name">Nome</label>
      <input type="text" name="name" id="name">
      <input type="submit" name="submit" value="Submit">
  </form>

  <a href="index.php">Back to home</a>

<?php require "templates/footer.php"; ?>
