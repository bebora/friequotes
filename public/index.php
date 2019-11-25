<?php
require '../common.php';
$is_admin = require_login(LoginLevel::ADMIN) == LoginResult::OK;
$config = get_config();
$pageTitle = $config->sitename . ' Home';
include 'templates/header.php';
?>

<ul>
    <?php
    $result = get_token_info($_COOKIE['token']);
    if (!isset($result['auth_level'])) {
        header("Location: login.php");
        die();
    }
    $auth = $result['auth_level'];
    if ($auth >= LoginLevel::MODERATOR) { ?>
	<li><a href="create.php"><strong>➕ Aggiungi personaggio</strong></a></li>
        <?php } ?>
	<li><a href="search.php"><strong>🔎 Cerca</strong></a></li>
    <li><a href="newpost.php"><strong>📝 Aggiungi citazione</strong></a></li>
    <li><a href="feed.php"><strong>📰 Leggi ultimi post</strong></a></li>
    <li><a href="search.php?query=@&noheading=true"><strong>👥 Elenco personaggi</strong></a></li>
    <?php if ($is_admin) {?>
    <li><a href="dashboard.php"><strong>⚙️ Dashboard</strong></a></li>
    <?php }?>
</ul>
<br>
<br>

<?php include 'templates/footer.php'; ?>