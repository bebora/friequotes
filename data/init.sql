CREATE TABLE IF NOT EXISTS entities (
	id INTEGER PRIMARY KEY,
	name TEXT NOT NULL,
	birthday TEXT,
	propicpath TEXT
);

CREATE TABLE IF NOT EXISTS tags (
	id INTEGER PRIMARY KEY,
	name TEXT NOT NULL,
	UNIQUE(name)
);

CREATE TABLE IF NOT EXISTS posts (
	id INTEGER PRIMARY KEY,
	title TEXT NOT NULL,
	description TEXT,
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	lastedit TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS postusertags (
	postid INTEGER,
	entityid INTEGER
);

CREATE TABLE IF NOT EXISTS posthashtags (
	postid INTEGER,
	tagid INTEGER
);

CREATE TABLE IF NOT EXISTS entitiesmedia (
	entityid INTEGER,
	mediapath TEXT,
	created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    username TEXT not NULL,
    userid INTEGER primary key,
    auth_level INTEGER NOT NULL,
    password TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS tokens (
    userid INTEGER,
    token TEXT unique
);
