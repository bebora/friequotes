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
                $user_in_db = $result[0];
                if (isset($_POST['resetpasstoken']) && $_POST['resetpasstoken'] === $user_in_db['reset_token']) {
                    $match = true;
                    $updated_user = array(
                        "userid" => $user_in_db['userid'],
                        "password" => password_hash($_POST["password"], PASSWORD_BCRYPT)
                    );
                    $sql = 'UPDATE users
                            SET reset_token = NULL, password = :password
                            WHERE userid = :userid';
                    $stm = $db->prepare($sql);
                    $stm->execute($updated_user);
                }
                else {
                    $match = password_verify($_POST['password'], $user_in_db['password']);
                }
                echo $match ? "Match" : "No match";
                if ($match) {
                    set_login_cookie($user_in_db['userid']);
                    header("Location: /index.php");
                    echo "Logged successfully";
                    die();
                }
                else {
                    header("Location: /login.php?wrong=1&username=" . urlencode($_POST['name']));
                }
            }

        }
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $prefilled_username = $_GET['username'] ?? "Guest";
    $pageTitle = 'Login';
    include 'templates/header.php'?>

    <form method="post">
        <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
        <?php if (isset($_GET['resetpasstoken'])) {
            echo '<input name="resetpasstoken" type="hidden" value="' . escape($_GET['resetpasstoken']) . '">';
        }?>
        <label for="name">Nome utente</label>
        <input type="text" name="name" id="name" value="<?php echo escape($prefilled_username)?>">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        <input type="submit" name="submit" value="Submit">
    </form>
    <?php
    if (isset($_GET['wrong'])) {
        echo '<div>❌ Username e/o password incorretti</div>';
    }
    include 'templates/footer.php';
}