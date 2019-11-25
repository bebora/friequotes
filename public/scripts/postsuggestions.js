var entities = [];
var entitiesDiv = document.getElementById('entdiv');
function createEntityElem(item) {
    let newEnt = document.createElement('span');
    newEnt.classList.add('entity-elem');
    newEnt.innerText = item.label;
    let removeButton = document.createElement('span');
    removeButton.innerText = '❌';
    removeButton.classList.add('remove-button');
    removeButton.onclick = removeEntity;
    newEnt.appendChild(removeButton);
    newEnt.dataset.userid = item.value;
    return newEnt;
}
function addEntity(item) {
    if (entities.includes(item.value)) {
        alert('Hai già inserito questo utente');
    }
    else {
        entities.push(item.value);
        entitiesDiv.appendChild(createEntityElem(item));
        hiddenEnts.value = entities.join(',');
    }
}

let autocompleteInput = document.getElementById('autocompInput');
let hiddenEnts = document.getElementById('hiddenEnts');
autocomplete({
    onSelect: function(item) {
        addEntity(item);
        autocompleteInput.value = '';
    },
    input: autocompleteInput,
    emptyMsg: 'Nessuna persona con questo nome',
    fetch: function(text, callback) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'suggestions.php?query=' + encodeURIComponent(text) , true);
        xhr.onload = function(e) {
            if (xhr.readyState === 4) {
                let results = JSON.parse(xhr.response);
                let filteredResults = results.filter(item => !entities.includes(item.value));
                callback(filteredResults);
            }
        };
        xhr.onerror = function () {
            console.error('Impossibile trovare suggerimenti');
        };
        xhr.send();
    },
    debounceWaitMs: 200,
    preventSubmit: true,
    minLength: 1
});

function removeEntity(e) {
    let toRemove = e.target.parentNode;
    console.log(entities);
    entities = entities.filter(i => i !== toRemove.dataset.userid);
    console.log(entities);
    hiddenEnts.value = entities.join(',');
    toRemove.parentNode.removeChild(toRemove);
}