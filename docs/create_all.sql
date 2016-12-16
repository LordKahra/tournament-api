CREATE TABLE notifications
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  type INT(11) NOT NULL,
  name VARCHAR(64) NOT NULL,
  body TEXT NOT NULL
);

CREATE TABLE locations
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  address_1 VARCHAR(255) NOT NULL,
  address_2 VARCHAR(255),
  city VARCHAR(255) NOT NULL,
  state VARCHAR(255) NOT NULL,
  zip VARCHAR(64) NOT NULL,
  country VARCHAR(255) DEFAULT 'US' NOT NULL
);

CREATE TABLE byes
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  round_id INT(11) NOT NULL,
  player_id BIGINT(20) NOT NULL,
  CONSTRAINT player_byes___fk FOREIGN KEY (player_id) REFERENCES players (dci),
  CONSTRAINT round_byes___fk FOREIGN KEY (round_id) REFERENCES rounds (id)
);
CREATE INDEX player_byes___fk ON byes (player_id);
CREATE INDEX round_byes___fk ON byes (round_id);
CREATE TABLE matches
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  round_id INT(11) NOT NULL,
  `table` INT(11) NOT NULL,
  draws INT(11) NOT NULL,
  CONSTRAINT rounds_matches___fk FOREIGN KEY (round_id) REFERENCES rounds (id)
);
CREATE INDEX round_matches___fk ON matches (round_id);
CREATE TABLE players
(
  dci BIGINT(20) PRIMARY KEY NOT NULL,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  middle_initial VARCHAR(8),
  country VARCHAR(8) NOT NULL,
  email VARCHAR(256)
);
CREATE TABLE rounds
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  tournament_id INT(11) NOT NULL,
  `index` INT(11) NOT NULL,
  CONSTRAINT rounds_tournament_id FOREIGN KEY (tournament_id) REFERENCES tournaments (id)
);
CREATE INDEX round_tournament_id ON rounds (tournament_id);
CREATE TABLE schedules
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  tournament_id INT(11) NOT NULL,
  day INT(11) NOT NULL,
  time INT(11) NOT NULL
);
CREATE TABLE seats
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  match_id INT(11) NOT NULL,
  player_id BIGINT(20) NOT NULL,
  wins INT(11) NOT NULL,
  CONSTRAINT match_seats___fk FOREIGN KEY (match_id) REFERENCES matches (id),
  CONSTRAINT player_seats___fk FOREIGN KEY (player_id) REFERENCES players (dci)
);
CREATE INDEX match_seats___fk ON seats (match_id);
CREATE INDEX player_seats___fk ON seats (player_id);
CREATE TABLE stores
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  name VARCHAR(128) NOT NULL,
  vanity_url VARCHAR(32),
  site TEXT,
  CONSTRAINT stores_user_id FOREIGN KEY (user_id) REFERENCES users (id)
);
CREATE UNIQUE INDEX stores_url_uindex ON stores (vanity_url);
CREATE INDEX store_user_id ON stores (user_id);
CREATE TABLE tournament_types
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  name VARCHAR(128) NOT NULL
);
CREATE TABLE tournaments
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  name VARCHAR(32) NOT NULL,
  last_updated INT(11) DEFAULT '100' NOT NULL,
  filename VARCHAR(32) DEFAULT 'test.wer' NOT NULL,
  store_id INT(11),
  type_id INT(11) DEFAULT '1',
  CONSTRAINT tournaments_store_id FOREIGN KEY (store_id) REFERENCES stores (id),
  CONSTRAINT types_tournaments___fk FOREIGN KEY (type_id) REFERENCES tournament_types (id)
);
CREATE INDEX tournament_store_id ON tournaments (store_id);
CREATE INDEX type_tournaments___fk ON tournaments (type_id);
CREATE TABLE uploads
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  tournament_id INT(11) NOT NULL,
  timestamp INT(11) NOT NULL,
  CONSTRAINT tournaments_uploads___fk FOREIGN KEY (tournament_id) REFERENCES tournaments (id)
);
CREATE INDEX tournament_uploads___fk ON uploads (tournament_id);
CREATE TABLE users
(
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  dci BIGINT(20) NOT NULL,
  email VARCHAR(256) NOT NULL,
  password VARCHAR(64) NOT NULL,
  is_subscribed TINYINT(1) DEFAULT '1' NOT NULL
);

# STORE -> LOCATION
# STORE -> USER

CREATE INDEX stores_locations_id_fk ON stores (location_id);
CREATE INDEX stores_user_id_ind ON stores (user_id);

# USER / dci
CREATE UNIQUE INDEX users_dci_uindex ON users (dci);

# STORE / vanity_url
CREATE UNIQUE INDEX stores_url_uindex ON stores (vanity_url);