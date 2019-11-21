<?php
require_once "common.php";
function render_media($item, $media_prefix) {
    return '<img src="' . $media_prefix . $item['mediapath'] . '" alt="' . basename($item['mediapath']) . '">';
}

function render_medias($result, $pagelimit, $media_prefix) {
    $strbuilder = '';
    for ($i=0; $i<$pagelimit; $i++) {
        $strbuilder .= render_media($result[$i], $media_prefix);
    }
    return $strbuilder;
}

function render_post($post) {
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

function render_user_searchpage($user) {
    return '
    <a href="userinfo.php?id=' . escape($user["id"]) . '" class="userboxcontainer">
        <div class="userbox">
            <img class="userthumb" src="uploads/thumbs/'. escape($user["propicpath"]) . '" alt="Foto profilo non trovata">
            <span>'
        . escape($user["name"]) . '
        </span>
        </div>
    </a>';
}

function render_posts($result, $pagelimit) {
    $strbuilder = '';
    for ($i=0; $i<$pagelimit; $i++) {
        $strbuilder .= render_post($result[$i]);
    }
    return $strbuilder;
}

function render_users_searchpage($result, $pagelimit) {
    $strbuilder = '';
    for ($i=0; $i<$pagelimit; $i++) {
        $strbuilder .= render_user_searchpage($result[$i]);
    }
    return '<div class="wrapper">' .$strbuilder . '</div>';
}

function render_token($token) {
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
        $author_info['username'],
        $token['created'],
        '❌'
    );
}

function render_tokens($tokens, $limit) {
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

    for ($i=0; $i<$limit; $i++) {
        $temp .= render_token($tokens[$i]);
    }
    $temp .= '</tbody></table>';
    return $temp;
}

function render_user_dashboard() {

}

function render_users_dashboard() {

}
?>