function fillpost(postid) {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'post.php?id='+postid+'&json=true', true);
    xhr.onload = function (e) {
        if (xhr.status === 200) {
            let info = JSON.parse(xhr.response);
            document.getElementById('title').value = info.title;
            document.getElementById('description').value = info.description;
            document.getElementById('tags').value = info['tags'].map(x => '#'+x.name).join(', ');
            for (let e of info.entities) {
                addEntity({
                    value: e.entityid,
                    label: e.name
                })
            }
        } else {
            alert('Impossibile caricare info sul post!');
        }
    };
    xhr.send();
}