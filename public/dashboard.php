<?php

require '../common.php';
require "../render.php";
check_token(LoginLevel::ADMIN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        echo "Token CSRF mancante";
        die();
    }
    $grant = $_POST['grantlevel'];
    if (isset($grant)) {
        if ($grant < 0)
            $grant = 0;
        if ($grant > 3)
            $grant = 3;
        $db = get_db();
        $check_existing_invite = $db->prepare('SELECT *
                                                    FROM invitetokens
                                                    WHERE token = :token');
        do {
            $token = gen_random_bytes();
            $check_existing_invite->execute(array(':token' => $token));
            $exist = $check_existing_invite->fetchAll();
        } while (count($exist) > 0);
        //Get creator of this invite
        $result = get_token_info($_COOKIE['token']);
        $new_invite = array(
            'token' => $token,
            'grantlevel' => $grant,
            'created' => date('c'),
            'author' => $result['userid']
        );
        $sql = sprintf(
            'INSERT INTO %s (%s) values (%s)',
            'invitetokens',
            implode(', ', array_keys($new_invite)),
            ':' . implode(', :', array_keys($new_invite))
        );
        $insert_token = $db->prepare($sql);
        $insert_token->execute($new_invite);
    }
    elseif (isset($_POST['revoketoken'])) {
        $db = get_db();
        $sql = 'DELETE
                FROM invitetokens
                WHERE token = :token';
        $stm = $db->prepare($sql);
        $stm->execute(array(":token" => $_POST['revoketoken']));
    }
}

$pageTitle = 'Dashboard inviti';
include 'templates/header.php';?>
<div>
    <div>
        <h3>
            Crea nuovo invito
        </h3>
    </div>
    <div>
        <form method='POST'>
            <select name='grantlevel'>
                <option value='0'>Guest</option>
                <option value='1'>Utente</option>
                <option value='2'>Moderatore</option>
                <option value='3'>Admin</option>
            </select>
            <input id="csrf" name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']);?>">
            <button type='submit' id='upload-button'>Crea</button
        </form>
    </div>
</div>
<h3>Inviti creati</h3>
<?php
$db = get_db();
$sql = 'SELECT *
        FROM invitetokens';
$stm = $db->prepare($sql);
$stm->execute();
$result = $stm->fetchAll();
echo render_tokens($result, count($result));?>
<script>
    function copyToClipboard(e) {
        let token = e.innerText;
        let inviteURL = new URL(window.location.href);
        inviteURL.pathname = '/register.php';
        inviteURL.searchParams.set('ref', token);
        console.log(inviteURL);
        let tempTextarea = document.createElement('textarea');
        tempTextarea.value = inviteURL;
        document.body.appendChild(tempTextarea);
        tempTextarea.focus();
        tempTextarea.select();
        document.execCommand('copy');
        document.body.removeChild(tempTextarea);
    }
    function revokeInvite(e) {
        let formData = new FormData();
        let csrftoken = document.getElementById('csrf').value;
        formData.append('csrf', csrftoken);
        formData.append('revoketoken', e.parentNode.children[0].innerText);
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'dashboard.php', false);
        xhr.send(formData);
        location.reload();
    }
</script>
<?php
include 'templates/footer.php';
