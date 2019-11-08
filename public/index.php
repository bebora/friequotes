<?php
$pageTitle = 'Friequotes Home';
include 'templates/header.php';
?>

<ul>
	<li><a href="create.php"><strong>➕ Aggiungi personaggio</strong></a> - add a user</li>
	<li><a href="search.php"><strong>🔎 Cerca</strong></a> - find a user or quote</li>
    <li><a href="newpost.php"><strong>📝 Aggiungi citazione</strong></a> - add a quote</li>
    <li><a href="feed.php"><strong>📰 Leggi ultimi post</strong></a> - read feed</li>
    <li><a href="search.php?query=@"><strong>👥 Elenco personaggi</strong></a> - list users</li>
</ul>
<?php include "templates/footer.php"; ?>