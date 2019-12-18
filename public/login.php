<?php
include '../common.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf'])) {
        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            echo 'Invalid CSRF';
            die();
        }
        if (isset($_POST['password']) && isset($_POST['name'])) {
            $db = get_db();
            $connection = $db->prepare("SELECT * 
                                                  FROM users
                                                  WHERE username = :username");
            $connection->execute(array(":username" => trim($_POST['name'])));
            $result = $connection->fetchAll();
            if (count($result) < 1) {
                header('Location: '. "/login.php");
                echo "Invalid username";
                die();
            }
            else {
                $match = password_verify($_POST['password'], $result[0]['password']);
                if ($match) {
                    set_login_cookie($result[0]['userid']);
                    header("Location: /index.php");
                    echo "Logged successfully";
                    die();
                }
            }

        }
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pageTitle = 'Login';
    include 'templates/header.php'?>

    <form method="post">
        <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
        <label for="name">Nome utente</label>
        <input type="text" name="name" id="name" value="Guest">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        <input type="submit" name="submit" value="Submit">
    </form>
    <?php
    include 'templates/footer.php';
}