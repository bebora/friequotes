<?php
$pageTitle = 'Friequotes Home';
include 'templates/header.php';
?>

<ul>
	<li><a href="create.php"><strong>➕ Aggiungi personaggio</strong></a></li>
	<li><a href="search.php"><strong>🔎 Cerca</strong></a></li>
    <li><a href="newpost.php"><strong>📝 Aggiungi citazione</strong></a></li>
    <li><a href="feed.php"><strong>📰 Leggi ultimi post</strong></a></li>
    <li><a href="search.php?query=@"><strong>👥 Elenco personaggi</strong></a></li>
    <br>
    <li><a href="login.php"><strong>Login</strong></a></li>
</ul>
<br>
<br>

<?php include "templates/footer.php"; ?>