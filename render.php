<?php
require_once "common.php";

function render_media($item, $media_prefix, $can_remove)
{
    return '<div class="img-container">
                <img src="' . $media_prefix . $item['mediapath'] . '" alt="' . basename($item['mediapath']) . '">' .
        ($can_remove ? '<span class="removemedia-button" data-mediaid="' . $item['mediaid'] . '">❌</span>' : '') .
        '</div>';
}

function render_medias($result, $pagelimit, $media_prefix, $can_remove)
{
    $strbuilder = '';
    for ($i = 0; $i < $pagelimit; $i++) {
        $strbuilder .= render_media($result[$i], $media_prefix, $can_remove);
    }
    return $strbuilder;
}

function render_post($post)
{
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

function render_user_searchpage($user)
{
    return '
    <a href="userinfo.php?id=' . escape($user["id"]) . '" class="userboxcontainer">
        <div class="userbox">
            <img class="userthumb" src="uploads/thumbs/' . escape($user["propicpath"]) . '" alt="Foto profilo non trovata">
            <span>'
        . escape($user["name"]) . '
        </span>
        </div>
    </a>';
}

function render_posts($result, $pagelimit)
{
    $strbuilder = '';
    for ($i = 0; $i < $pagelimit; $i++) {
        $strbuilder .= render_post($result[$i]);
    }
    return $strbuilder;
}

function render_users_searchpage($result, $pagelimit)
{
    $strbuilder = '';
    for ($i = 0; $i < $pagelimit; $i++) {
        $strbuilder .= render_user_searchpage($result[$i]);
    }
    return '<div class="wrapper">' . $strbuilder . '</div>';
}

function render_token($token)
{
    $grantlevel_to_string = array('Guest', 'Utente', 'Moderatore', 'Admin');
    $sql = 'SELECT * FROM users WHERE userid = :userid';
    $db = get_db();
    $stm = $db->prepare($sql);
    $stm->execute(array(':userid' => $token['author']));
    $author_info = $stm->fetch();
    return sprintf('
        <tr>
            <td title="Copia link d\'invito 📋" style="cursor: copy;" onclick="copyToClipboard(this)">%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td style="text-align: center; cursor: pointer;" onclick="revokeInvite(this)">%s</td>
        </tr>
        ', $token['token'],
        $grantlevel_to_string[$token['grantlevel']],
        escape($author_info['username']),
        $token['created'],
        '❌'
    );
}

function render_tokens($tokens, $limit)
{
    $temp = '<table class="tokendash">
                <thead>
                    <tr>
                        <th class="column">Token</th>
                        <th class="shortcolumn">Livello</th>
                        <th class="column">Creato da</th>
                        <th class="shortcolumn">Creato il</th>
                        <th>Revoca</th>
                    </tr>
                 </thead><tbody>';

    for ($i = 0; $i < $limit; $i++) {
        $temp .= render_token($tokens[$i]);
    }
    $temp .= '</tbody></table>';
    return $temp;
}

function render_user_line($user): string
{
    $grantlevel_to_string = array('Guest', 'Utente', 'Moderatore', 'Admin');
    return sprintf('
        <a href="manageuser.php?userid=%s">
    <span>%s</span>
    <span>%s</span>
  </a>',
        $user['userid'],
        escape($user['username']),
        $grantlevel_to_string[$user["auth_level"]]
    );
}

function render_reset_password($token, $userid, $username): string
{
    if (is_null($token)) {
        return sprintf('
<span>Link di reset password non esistente, clicca il bottone per crearlo</span>
<form id="resetpassform" method="post">
<input id="csrftoken" name="csrf" type="hidden" value="%s">
<input name="userid" type="hidden" value="%s">
<input name="generate_reset_token" value="Crea link" type="submit">
</form>
', escape($_SESSION['csrf']), $userid);
    } else {
        return sprintf('<a href="/login.php?resetpasstoken=%s&username=%s">🔑 Link </a>', escape($token), escape($username));
    }
}

function render_complete_user_dashboard($user): string
{
    $grantlevel_to_string = array('Guest', 'Utente', 'Moderatore', 'Admin');
    $select = '<select form="userform" name="authlevel">';
    for ($j = 0; $j < 4; $j++) {
        $select .= '<option value="' . $j . ($j == $user['auth_level'] ? '" selected>' : '">') . $grantlevel_to_string[$j] . '</option>';
    }
    $select .= '</select>';

    return sprintf('
<h3>Info utente</h3>
<div>
    <span>Nome utente:</span>
    <span><b>%s</b></span>
</div>
<div>
    <span>ID:</span>
    <span>%s</span>
</div>
<div>
    <h3>Ruolo</h3>
    <form method="post" id="userform">
        <input id="csrftoken" name="csrf" type="hidden" value="%s">
        <input name="userid" type="hidden" value="%s">
        <span>%s</span>
        <input type="submit" name="submit" value="Conferma">
    </form>
    <h3>Link reset password</h3>
    %s
</div>',
        escape($user['username']),
        $user["userid"],
        escape($_SESSION['csrf']),
        $user["userid"],
        $select,
        render_reset_password($user['reset_token'], $user["userid"], $user["username"])
    );
}

function render_users_list($items, $limit): string
{
    $temp = '
    <div class="userTable">
    <div>
    <span>Nome utente</span>
    <span>Livello</span>
</div>
    ';
    for ($i = 0; $i < $limit; $i++) {
        $temp .= render_user_line($items[$i]);
    }
    $temp .= '</div>';
    return $temp;
}

function render_hashtag($item)
{
    return sprintf('
        <a href="hashtagfeed.php?id=%d"><span class="hashtag">#%s</span></a>',
        $item['id'],
        escape($item['name'])
    );
}

function render_hashtags($items, $limit, $inline = false, $pretext = null)
{
    $temp = sprintf(
        '<div%s>%s',
        $inline ? ' class="hashtag-container-inline"' : ' class="hashtag-container"',
        $pretext != null ? sprintf('<span>%s</span>', escape($pretext)) : ''
    );
    for ($i = 0; $i < $limit; $i++) {
        $temp .= render_hashtag($items[$i]);
    }
    $temp .= '</div>';
    return $temp;
}

?>
