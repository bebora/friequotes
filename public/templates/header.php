<?php
define('__ROOT__', dirname(__FILE__, 3));
require_once __ROOT__.'/common.php';
$config = get_config();
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?></title>
	<link rel="stylesheet" href="/css/style.css">
    <?php echo isset($extrastyle)? $extrastyle : '' ?>
    <?php echo isset($scripts)? $scripts : '' ?>
</head>

<body>
	<h1 style="display: inline-block"><a href="/index.php"><?php echo $config->sitename?> 📜</a></h1>
<?php
if(isset($_COOKIE['token'])) {
    $db = get_db();
    $infosql = 'SELECT *
            FROM users u
            LEFT JOIN tokens t on t.userid = u.userid
            WHERE t.token = :token
            ';
    $infostm = $db->prepare($infosql);
    $infostm->execute(array(':token' => $_COOKIE['token']));
    $inforesult = $infostm->fetch();
    if ($inforesult != null) {
        echo sprintf('<span style="float: right;"><a href="/login.php" title="Loggati come un altro utente">%s - %s</a><a href="/logout.php" style="margin-left: 20px; margin-right: 5px;">Esci</a></span>',
            escape($inforesult['username']),
            array('Guest 👀', 'Utente 👤', 'Moderatore 👮', 'Admin 👑')[$inforesult['auth_level']]);
    }
}
else {
    echo '<span style="float: right;"><a href="/login.php" title="Effettua il login">Login</a></span>';
}
?>
