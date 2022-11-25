<?php
require '../common.php';
$is_admin = require_login(LoginLevel::ADMIN) == LoginResult::OK;
$config = get_config();
$pageTitle = $config->sitename . ' Home';
include 'templates/header.php';
?>

<ul>
    <?php
    if (!isset($_COOKIE['token'])) {
        header("Location: /login.php");
        die();
    }
    $result = get_token_info($_COOKIE['token']);
    if (!isset($result['auth_level'])) {
        header("Location: login.php");
        die();
    }
    $auth = $result['auth_level'];
    if ($auth >= LoginLevel::MODERATOR) { ?>
        <li><a href="create.php"><strong>➕ Aggiungi personaggio</strong></a></li>
    <?php }
    ?>
    <li><a href="search.php"><strong>🔎 Cerca</strong></a></li>
    <li><a href="newpost.php"><strong>📝 Aggiungi citazione</strong></a></li>
    <li><a href="feed.php"><strong>📰 Leggi ultimi post</strong></a></li>
    <li><a href="search.php?query=@&noheading=true"><strong>👥 Elenco personaggi</strong></a></li>
    <?php if ($is_admin) {?>
    <li><a href="admin/dashboard.php"><strong>⚙️ Dashboard</strong></a></li>
    <?php }?>
</ul>
<br>
<br>
<?php //Show countdown if configured
if (isset($config->next_event->name) && isset($config->next_event->timestamp)) {
    if (time() < $config->next_event->timestamp) { ?>
        <h1>Countdown <?php echo $config->next_event->name ?></h1>
        <div class="parentcountdown" id="parentcountdown">
            <div class="childcountdown">
                <div id="days" style="font-size: 52px">--</div>
                <div class="line-break"></div>
                <div>Giorni</div>
            </div>
            <div class="childcountdown">
                <div id="hours" style="font-size: 52px">--</div>
                <div class="line-break"></div>
                <div>Ore</div>
            </div>
            <div class="childcountdown">
                <div id="minutes" style="font-size: 52px">--</div>
                <div class="line-break"></div>
                <div>Minuti</div>
            </div>
            <div class="childcountdown">
                <div id="seconds" style="font-size: 52px">--</div>
                <div class="line-break"></div>
                <div>Secondi</div>
            </div>
        </div>
        <img src="<?php echo $config->next_event->image ?>" style="width: 100%" alt="<?php echo $config->next_event->name ?>">
        <script>
            //https://gist.github.com/adriennetacke/f5a25c304f1b7b4a6fa42db70415bad2#file-countdown-js
            function countdown(endDate) {
                let days, hours, minutes, seconds;

                endDate = new Date(endDate).getTime();
                if (isNaN(endDate)) {
                    return;
                }
                let intervalHandle = setInterval(calculate, 1000);

                function calculate() {
                    let startDate = new Date().getTime();

                    let timeRemaining = parseInt((endDate - startDate) / 1000);

                    if (timeRemaining >= 0) {
                        days = parseInt(timeRemaining / 86400);
                        timeRemaining = (timeRemaining % 86400);

                        hours = parseInt(timeRemaining / 3600);
                        timeRemaining = (timeRemaining % 3600);

                        minutes = parseInt(timeRemaining / 60);
                        timeRemaining = (timeRemaining % 60);

                        seconds = parseInt(timeRemaining);

                        document.getElementById("days").innerHTML = parseInt(days, 10);
                        document.getElementById("hours").innerHTML = hours < 10 ? "0" + hours : hours;
                        document.getElementById("minutes").innerHTML = minutes < 10 ? "0" + minutes : minutes;
                        document.getElementById("seconds").innerHTML = seconds < 10 ? "0" + seconds : seconds;
                    }
                    else {
                        document.getElementById("parentcountdown").innerHTML = '<h2><?php echo $config->next_event->end_phrase ?></h2>';
                        clearInterval(intervalHandle);
                    }
                }
            }

            (function () {
                countdown(<?php echo $config->next_event->timestamp ?>000);
            }());
        </script>
    <?php }
}?>
<?php include 'templates/footer.php'; ?>
