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

function render_user($user) {
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

function render_users($result, $pagelimit) {
    $strbuilder = '';
    for ($i=0; $i<$pagelimit; $i++) {
        $strbuilder .= render_user($result[$i]);
    }
    return '<div class="wrapper">' .$strbuilder . '</div>';
}

?>