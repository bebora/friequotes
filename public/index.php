<?php
require '../common.php';
$is_admin = require_login(LoginLevel::ADMIN) == LoginResult::OK;
$config = get_config();
$pageTitle = $config->sitename . ' Home';
include 'templates/header.php';
?>

<ul>
	<li><a href="create.php"><strong>➕ Aggiungi personaggio</strong></a></li>
	<li><a href="search.php"><strong>🔎 Cerca</strong></a></li>
    <li><a href="newpost.php"><strong>📝 Aggiungi citazione</strong></a></li>
    <li><a href="feed.php"><strong>📰 Leggi ultimi post</strong></a></li>
    <li><a href="search.php?query=@"><strong>👥 Elenco personaggi</strong></a></li>
    <?php if ($is_admin) {?>
    <li><a href="dashboard.php"><strong>⚙️ Dashboard</strong></a></li>
    <?php }?>
</ul>
<br>
<br>

<?php include 'templates/footer.php'; ?>