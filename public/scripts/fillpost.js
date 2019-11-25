function fillpost(postid) {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'post.php?id='+postid+'&json=true', true);
    xhr.onload = function (e) {
        if (xhr.status === 200) {
            console.log(xhr.response);
        } else {
            alert('Impossibile caricare info sul post!');
        }
    };
    xhr.send();
}