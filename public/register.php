<?php
//TODO Add a proper way to manage registration
include '../common.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf'])) {
        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            echo 'Invalid CSRF';
            die();
        }
        if (isset($_POST['password']) && isset($_POST['name']) && isset($_POST['unlock']) && $_POST['unlock']=="wildcard") {
            $db = get_db();
            //TODO check existing username
            $new_user = array(
                "username" => $_POST["name"],
                "password" => password_hash($_POST["password"], PASSWORD_BCRYPT),
                "auth_level" => LoginLevel::MODERATOR
            );
            $sql = sprintf(
                "INSERT INTO %s (%s) values (%s)",
                "users",
                implode(", ", array_keys($new_user)),
                ":" . implode(", :", array_keys($new_user))
            );
            $connection = $db->prepare($sql);
            $connection->execute($new_user);
            header("Location: /index.php");
            echo "Added new user as moderator";
        }
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pageTitle = 'Register';
    include 'templates/header.php'?>
    Temp page
    <form method="post">
        <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
        <label for="name">Nome utente</label>
        <input type="text" name="name" id="name" value="Guest">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        <label for="unlock">Password</label>
        <input type="text" name="unlock" id="unlock">
        <input type="submit" name="submit" value="Submit">
    </form>
    <?php
    include 'templates/footer.php';
}