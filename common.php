<?php
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = gen_random_bytes();
}

function gen_random_bytes() {
    $length = 32;
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    }
    if (function_exists('mcrypt_create_iv')) {
        return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }
    else {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}

function get_db() {
    return new PDO("sqlite:" . dirname(__FILE__) .  "/data/db.sqlite");
}

/**
 * Escapes HTML for output
 *
 */

function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function get_relevant_date_diff($date) {
    try {
        $diff = date_diff(new DateTime(), new DateTime($date));
        if ($diff->y > 0) {
            return $diff->format($diff->y > 1 ? '%y anni' : '1 anno');
        }
        elseif ($diff->m > 0) {
            return $diff->format($diff->m > 1 ? '%m mesi' : '1 mese');
        }
        elseif ($diff->d > 0) {
            return $diff->format($diff->d > 1 ? '%d giorni' : '1 giorno');
        }
        elseif ($diff->h > 0) {
            return $diff->format($diff->h > 1 ? '%h ore' : '1 ora');
        }
        elseif ($diff->i > 0) {
            return $diff->format($diff->i > 1 ? '%i minuti' : '1 minuto');
        }
        else {
            return $diff->format($diff->s > 1 ? '%s secondi' : '1 secondo');
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        return '';
    }
}

function renderpost($post) {
    return '
    <a href="post.php?id=' . escape($post["id"]) . '" class="nounderline">
        <div class="postbox">
            <div class="posttitle" style="position: relative;">
                <span class="titlecard1">' . escape($post["title"]) . '</span>
                <span class="timetitlecard">' . get_relevant_date_diff($post["created"]) . ' fa</span>
            </div>
            <p class="postdesc">' . escape($post["description"]) . '</p>
        </div>
    </a>
    ';
}

function renderuser($user) {
    $imageFileType = strtolower(pathinfo($user["propicpath"],PATHINFO_EXTENSION));
    return '
    <a href="userinfo.php?id=' . escape($user["id"]) . '" class="userboxcontainer">
        <div class="userbox">
            <img class="userthumb" src="uploads/'. escape($user["propicpath"]). '.thumb.'. $imageFileType . '" alt="Foto profilo non trovata">
            <span>'
        . escape($user["name"]) . '
        </span>
        </div>
        
    </a>';
}

function renderposts($result, $pagelimit) {
    $strbuilder = '';
    for ($i=0; $i<$pagelimit; $i++) {
        $strbuilder .= renderpost($result[$i]);
    }
    echo $strbuilder;
    return $strbuilder;
}

function renderusers($result, $pagelimit) {
    $strbuilder = '';
    for ($i=0; $i<$pagelimit; $i++) {
        $strbuilder .= renderuser($result[$i]);
    }
    return '<div class="wrapper">' .$strbuilder . '</div>';
}

class LoginLevel
{
    const GUEST = 0;
    const USER = 1;
    const MODERATOR = 2;
    const ADMIN = 3;
}

class LoginResult
{
    const OK = 0;
    const LEVEL_LOW = 1;
    const NOT_LOGGED = 2;
    const INVALID_TOKEN = 3;
}

function require_login($min_level) {
    if(!isset($_COOKIE['token'])) {
        return LoginResult::NOT_LOGGED;
    }
    else {
        $result = get_token_info($_COOKIE['token']);
        if (isset($result['auth_level'])) {
            if ($result['auth_level'] < $min_level) {
                return LoginResult::LEVEL_LOW;
            }
            elseif ($result['auth_level'] >= $min_level) {
                return LoginResult::OK;
            }
            else {
                return LoginResult::INVALID_TOKEN;
            }
        }
        else {
            return LoginResult::INVALID_TOKEN;
        }
    }
}

function get_token_info($token) {
    $db = get_db();
    $sql = 'SELECT *
            FROM tokens
            JOIN users u on tokens.userid = u.userid
            WHERE token = :token';
    $connection = $db->prepare($sql);
    $connection->execute(array(':token' => $token));
    $result = $connection->fetch();
    return $result;
}

function check_token($min_level) {
    $login = require_login($min_level);
    if ($login == LoginResult::INVALID_TOKEN or $login == LoginResult::NOT_LOGGED) {
        header('Location: '. "/login.php");
        echo "Invalid token";
        die();
    }
    elseif ($login == LoginResult::LEVEL_LOW) {
        http_response_code(403);
        echo "Unauthorized";
        die();
    }
}

function set_login_cookie($userid) {
    $db = get_db();
    $check_existing_token = $db->prepare("SELECT *
                                                    FROM tokens
                                                    WHERE token = :token");
    do {
        $token = gen_random_bytes();
        $check_existing_token->execute(array(":token" => $token));
        $exist = $check_existing_token->fetchAll();
    } while (count($exist) > 0);
    $new_token = array(
        "userid" => $userid,
        "token" => $token,
        "created" => date("c")
    );
    $sql = sprintf(
        "INSERT INTO %s (%s) values (%s)",
        "tokens",
        implode(", ", array_keys($new_token)),
        ":" . implode(", :", array_keys($new_token))
    );
    $insert_token = $db->prepare($sql);
    $insert_token->execute($new_token);
    setcookie("token", $token, time() + (2 * 365 * 24 * 60 * 60));
}