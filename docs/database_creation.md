# Database Scripts

### Creation

```
CREATE TABLE tournament_types
(
  id INT(11) PRIMARY KEY NOT NULL,
  name VARCHAR(128) NOT NULL
);

CREATE TABLE players
(
  dci BIGINT(20) PRIMARY KEY NOT NULL,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  middle_initial VARCHAR(8),
  country VARCHAR(8) NOT NULL,
  email VARCHAR(256)
);

CREATE TABLE users
(
  id INT(11) PRIMARY KEY NOT NULL,
  dci BIGINT(20) NOT NULL,
  email VARCHAR(256) NOT NULL,
  password VARCHAR(64) NOT NULL,
  is_subscribed TINYINT(1) DEFAULT '1' NOT NULL
);
CREATE UNIQUE INDEX users_dci_uindex ON users (dci);
CREATE UNIQUE INDEX users_email_uindex ON users (email);

CREATE TABLE stores
(
  id INT(11) PRIMARY KEY NOT NULL,
  user_id INT(11) NOT NULL,
  name VARCHAR(128) NOT NULL,
  vanity_url VARCHAR(32),
  site TEXT,
  CONSTRAINT stores_user_id__fk FOREIGN KEY (user_id) REFERENCES users (id)
);
CREATE UNIQUE INDEX stores_url_uindex ON stores (vanity_url);
CREATE INDEX stores_user_id_ind ON stores (user_id);

CREATE TABLE tournaments
(
  id INT(11) PRIMARY KEY NOT NULL,
  name VARCHAR(32) NOT NULL,
  last_updated INT(11) DEFAULT '100' NOT NULL,
  filename VARCHAR(32) DEFAULT 'test.wer' NOT NULL,
  store_id INT(11),
  type_id INT(11) DEFAULT '1',
  CONSTRAINT tournaments_store_id__fk FOREIGN KEY (store_id) REFERENCES stores (id),
  CONSTRAINT types_tournaments___fk FOREIGN KEY (type_id) REFERENCES tournament_types (id)
);
CREATE INDEX tournaments_store_id__index ON tournaments (store_id);
CREATE INDEX types_tournaments__index ON tournaments (type_id);

CREATE TABLE uploads
(
  id INT(11) PRIMARY KEY NOT NULL,
  tournament_id INT(11) NOT NULL,
  timestamp INT(11) NOT NULL,
  CONSTRAINT tournaments_uploads___fk FOREIGN KEY (tournament_id) REFERENCES tournaments (id)
);
CREATE INDEX tournaments_uploads__index ON uploads (tournament_id);

CREATE TABLE rounds
(
  id INT(11) PRIMARY KEY NOT NULL,
  tournament_id INT(11) NOT NULL,
  `index` INT(11) NOT NULL,
  CONSTRAINT rounds_tournament_id__fk FOREIGN KEY (tournament_id) REFERENCES tournaments (id)
);
CREATE INDEX rounds_tournament_id__index ON rounds (tournament_id);

CREATE TABLE matches
(
  id INT(11) PRIMARY KEY NOT NULL,
  round_id INT(11) NOT NULL,
  `table` INT(11) NOT NULL,
  draws INT(11) NOT NULL,
  CONSTRAINT rounds_matches___fk FOREIGN KEY (round_id) REFERENCES rounds (id)
);
CREATE INDEX rounds_matches__index ON matches (round_id);

CREATE TABLE byes
(
  id INT(11) PRIMARY KEY NOT NULL,
  round_id INT(11) NOT NULL,
  player_id BIGINT(20) NOT NULL,
  CONSTRAINT players_byes___fk FOREIGN KEY (player_id) REFERENCES players (dci),
  CONSTRAINT rounds_byes___fk FOREIGN KEY (round_id) REFERENCES rounds (id)
);
CREATE INDEX players_byes__index ON byes (player_id);
CREATE INDEX rounds_byes__index ON byes (round_id);

CREATE TABLE seats
(
  id INT(11) PRIMARY KEY NOT NULL,
  match_id INT(11) NOT NULL,
  player_id BIGINT(20) NOT NULL,
  wins INT(11) NOT NULL,
  CONSTRAINT matchs_seats___fk FOREIGN KEY (match_id) REFERENCES matches (id),
  CONSTRAINT players_seats___fk FOREIGN KEY (player_id) REFERENCES players (dci)
);
CREATE INDEX matchs_seats__index ON seats (match_id);
CREATE INDEX players_seats__index ON seats (player_id);

CREATE TABLE schedules
(
  id INT(11) PRIMARY KEY NOT NULL,
  tournament_id INT(11) NOT NULL,
  day INT(11) NOT NULL,
  time INT(11) NOT NULL
);
```

### Test Data



