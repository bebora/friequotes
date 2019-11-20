var uploadmedia = function(type) {
    let form = document.getElementById('file-form');
    let fileSelect = document.getElementById('file-select');
    let uploadButton = document.getElementById('upload-button');
    let progress = document.getElementById('progress');
    let csrftoken = document.getElementById('csrftoken').value;
    form.onsubmit = function(event) {
        event.preventDefault();
        let progressdiv = document.getElementById('progressdiv');
        progress.style.display = "block";
        uploadButton.innerHTML = 'Caricando...';
        let files = fileSelect.files;
        let formData = new FormData();
        let file = files[0];
        formData.append('fileToUpload', file, file.name);
        formData.append('type', type);
        formData.append('csrf', csrftoken);
        let urlParams = new URLSearchParams(window.location.search);
        formData.append('id', urlParams.get('id'));
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'uploadmedia.php', true);
        xhr.upload.onprogress = function (e) {
            update_progress(e);
        };
        xhr.onload = function (e) {
            if (xhr.status === 200) {
                uploadButton.innerHTML = 'Carica';
                let successtext = document.createElement("h3");
                successtext.innerText = 'Caricato media con successo! Potrebbe comunque non essere disponibile se eccede il limite di spazio consentito (5 MB)';
                progressdiv.appendChild(successtext);
                setTimeout(function(){ progressdiv.removeChild(successtext); progress.style.display = "none"; }, 3000);
            } else {
                alert('Impossibile caricare il media!');
            }
        };
        xhr.send(formData);
    };
    function update_progress(e){
        if (e.lengthComputable){
            let percentage = Math.round((e.loaded/e.total)*100);
            progress.value = percentage;
            uploadButton.innerHTML = 'Upload '+percentage+'%';
        }
        else{
            console.log("Unable to compute progress information since the total size is unknown");
        }
    }
};
