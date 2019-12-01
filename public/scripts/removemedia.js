let csrftoken = document.getElementById('csrftoken').value;
function removeMedia(clickedButton, media_type) {
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('mediaid', clickedButton.dataset.mediaid);
    formData.append('media_type', media_type);
    formData.append('csrf', csrftoken);
    xhr.open('POST', 'removemedia.php', true);
    xhr.onload = function (e) {
        if (xhr.status === 200) {
            let parent = clickedButton.parentNode;
            parent.removeChild(clickedButton);
            parent.innerText = 'Media rimosso';
            setTimeout(function () {
                parent.parentNode.removeChild(parent);
            },
                3000)
        } else {
            alert('Impossibile cancellare il media!');
        }
    };
    console.log(xhr);
    xhr.send(formData);
}

function addRemoveMediaListeners(media_type) {
    let removeButtons = document.getElementsByClassName('removemedia-button');
    Array.from(removeButtons).forEach(function(element) {
        element.addEventListener('click', function () {
            if (confirm('Vuoi davvero cancellare questo media?')) {
                removeMedia(this, media_type);
            }
        });
    });
}