<?php
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();

if (empty($_SESSION['csrf'])) {
	if (function_exists('random_bytes')) {
		$_SESSION['csrf'] = bin2hex(random_bytes(32));
	} else if (function_exists('mcrypt_create_iv')) {
		$_SESSION['csrf'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
	} else {
		$_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
	}
}

/**
 * Escapes HTML for output
 *
 */

function getdb() {
    return new PDO("sqlite:" . dirname(__FILE__) .  "/data/db.sqlite");
}
function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function getrelevantdatediff($date) {
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
                <span class="timetitlecard">' . getrelevantdatediff($post["created"]) . ' fa</span>
            </div>
            <p class="postdesc">' . escape($post["description"]) . '</p>
        </div>
    </a>
    ';
}

function renderposts($result, $pagelimit) {
    $strbuilder = '';
    for ($i=0; $i<$pagelimit; $i++) {
        $strbuilder .= renderpost($result[$i]);
    }
    return $strbuilder;
}


