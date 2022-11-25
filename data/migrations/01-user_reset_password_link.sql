PRAGMA foreign_keys = OFF;

BEGIN transaction;

CREATE TABLE "new_users"
(
    "username"    TEXT    NOT NULL UNIQUE,
    "userid"      INTEGER,
    "auth_level"  INTEGER NOT NULL,
    "password"    TEXT    NOT NULL DEFAULT 'changeme',
    "reset_token" TEXT,
    PRIMARY KEY ("userid")
);

INSERT INTO "new_users"("username", "userid", "auth_level", "password")
SELECT "username", "userid", "auth_level", "password"
FROM "users";

DROP TABLE users;

ALTER TABLE new_users
    RENAME TO "users";

PRAGMA FOREIGN_KEY_CHECK;

COMMIT;

PRAGMA foreign_keys = ON;
