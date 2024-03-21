# Friequotes
Friequotes è un piccolo tentativo di social network per raccogliere citazioni e storie memorabili fra amici.

Il backend è in PHP, con database gestito da SQLite. Il frontend è in HTML, con alcune funzionalità realizzate in Javascript.

Per l'autocompletamento nelle ricerche di entità è stato usato [autocomplete](https://github.com/kraaden/autocomplete).

La grafica è molto base, con CSS scritto a mano, ma è sufficientemente responsive su alcune pagine.

## Permessi
Friequotes ha 4 tipi diversi di utenti, ognuno con certi permessi e restrizioni sulle funzionalità utilizzabili

|                                | Admin | Moderatore | Utente | Guest |
|--------------------------------|:-----:|:----------:|:------:|:-----:|
| Leggere post                   |   X   |      X     |    X   |   X   |
| Scrivere post                  |   X   |      X     |    X   |       |
| Eliminare post                 |   X   |      X     |        |       |
| Aggiungere entità              |   X   |      X     |        |       |
| Modificare entità              |   X   |      X     |        |       |
| Modificare post                |   X   |      X     |        |       |
| Aggiungere foto ai post        |   X   |      X     |    X   |       |
| Aggiungere foto profilo entità |   X   |      X     |        |       |
| Invitare altri utenti          |   X   |            |        |       |
| Cercare post/entità/hashtag    |   X   |      X     |    X   |   X   |
| Cambiare permessi altrui       |   X   |            |        |       |
| Visualizzare info entità       |   X   |      X     |    X   |   X   |

## Registrazione
Per registrarsi è necessario ricevere un link di invito dall'admin, che sceglie i permessi del nuovo utente in fase di creazione del link.  
Quando non c'è nessun utente registrato, allora il primo utente a registrarsi sarà admin e non avrà bisogno di link di invito per procedere.

Ogni utente può scegliere username (con il vincolo di non avere due username uguali fra più utenti) e password. La password è conservata come hash Bcrypt. Attualmente non è possibile cambiare password autonomamente, ma l'admin può generare un link di reset password.

## Installazione
Friequotes è testato solo su Linux con PHP 7.  
Per controllare che tutte le dipendenze per il backend siano installate, eseguire `./checkenviron.sh`. Installare le eventuali dipendenze mancanti e successivamente eseguire `php install.php`.   

## Note
L'idea è nata dal voler gestire citazioni in un sistema facilmente accessibile da un gruppo di amici, che permettesse di collegare anche più persone ad una stessa citazione.  
Il frontend in HTML è stato scelto per la facilità di utilizzo per qualsiasi utente, il backend in PHP è stato scelto perché tipico per creare siti web e per vedere un linguaggio ancora non conosciuto dallo sviluppatore (l'alternativa poteva essere Node.js, che però si basa su Javascript, che lo sviluppatore ha già usato per altri progetti).  
Il progetto serve inoltre a esercitarsi su SQL.  
La versione iniziale ha preso spunto da un [tutorial online](https://www.taniarascia.com/create-a-simple-database-app-connecting-to-mysql-with-php/) per realizzare un'app CRUD in PHP.
