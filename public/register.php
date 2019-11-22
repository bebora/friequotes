<?php
include '../common.php';
function is_user_table_empty() {
    $db = get_db();
    $sql = "SELECT count(*) FROM users";
    $connection = $db->prepare($sql);
    $connection->execute();
    $usercount = $connection->fetch();
    return $usercount["count(*)"] == 0;
}

$alert = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf'])) {
        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            header("Location: /index.php");
            echo 'Invalid CSRF';
            die();
        }
        if (isset($_POST['password']) && isset($_POST['name'])) {
            $db = get_db();
            if (is_user_table_empty()) {
                $admin = array(
                    "username" => $_POST["name"],
                    "password" => password_hash($_POST["password"], PASSWORD_BCRYPT),
                    "auth_level" => LoginLevel::ADMIN
                );
                $sql = sprintf(
                    "INSERT INTO %s (%s) values (%s)",
                    "users",
                    implode(", ", array_keys($admin)),
                    ":" . implode(", :", array_keys($admin))
                );
                $connection = $db->prepare($sql);
                $connection->execute($admin);
                //Get new user id
                $sql = "SELECT * FROM users WHERE username = :username";
                $stm = $db->prepare($sql);
                $stm->execute(array(":username" => $_POST["name"]));
                $addeduser = $stm->fetch();
                set_login_cookie($addeduser["userid"]);
                header("Location: /index.php");
                echo "Registered successfully ad admin";
                die();
            }
            else {
                //Check if provided invite link is valid
                if (isset($_GET["ref"])) {
                    $invite = $_GET["ref"];
                    $sql = "SELECT *
                        FROM invitetokens
                        WHERE token = :token";
                    $stm = $db->prepare($sql);
                    $stm->execute(array(":token" => $invite));
                    $result = $stm->fetch();
                    if ($result != null) {
                        $sql = "SELECT * FROM users WHERE username = :username";
                        $stm = $db->prepare($sql);
                        $stm->execute(array(":username" => $_POST["name"]));
                        $resultuser = $stm->fetch();
                        if ($resultuser == null) {
                            $new_user = array(
                                "username" => $_POST["name"],
                                "password" => password_hash($_POST["password"], PASSWORD_BCRYPT),
                                "auth_level" => $result["grantlevel"]
                            );
                            $sql = sprintf(
                                "INSERT INTO %s (%s) values (%s)",
                                "users",
                                implode(", ", array_keys($new_user)),
                                ":" . implode(", :", array_keys($new_user))
                            );
                            $stm = $db->prepare($sql);
                            $stm->execute($new_user);
                            //Get new user id
                            $sql = "SELECT * FROM users WHERE username = :username";
                            $stm = $db->prepare($sql);
                            $stm->execute(array(":username" => $_POST["name"]));
                            $addeduser = $stm->fetch();
                            //Invite link should be removed after successful registration
                            $sql = 'DELETE from invitetokens WHERE token = :token';
                            $stm = $db->prepare($sql);
                            $stm->execute(array(":token" => $invite));
                            set_login_cookie($addeduser["userid"]);
                            header("Location: /index.php");
                            echo "Registered successfully";
                            die();
                        } else {
                            $alert = "Esiste già un utente con questo soprannome! Trovane un altro";
                        }
                    }
                    else {
                        $alert = "Il link di invito è scaduto, invalido o già usato.\n";
                    }
                }
                else {
                    $alert = "Il link di invito è incompleto.\n";
                }
            }
        }
        else {
            $alert = "Mancano username o password!\n";
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $alert != "") {
    if (isset($_GET["ref"])) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $invite = $_GET["ref"];
            $db = get_db();
            $sql = "SELECT *
                        FROM invitetokens
                        WHERE token = :token";
            $stm = $db->prepare($sql);
            $stm->execute(array(":token" => $invite));
            $result = $stm->fetch();
            if ($result == null) {
                $alert .= "\nL'URL che stai cercando non corrisponde ad un invito valido.";
            }
        }
    }
    else {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $alert .= "Non ci si può registrare senza un link d'invito! Se lo hai, apri il link completo";
        }
    }
    $pageTitle = 'Registrazione';
    include 'templates/header.php';
    echo $alert;?>
    <form method="post">
        <input id="csrftoken" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
        <label for="name">Nome utente</label>
        <input type="text" name="name" id="name">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        <input type="submit" name="submit" value="Submit">
    </form>
    <?php
    include 'templates/footer.php';
}