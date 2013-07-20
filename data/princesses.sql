CREATE TABLE princesses (
	id INTEGER NOT NULL,
	name VARCHAR(64) NOT NULL,
	married INTEGER, -- This is a boolean
	birth_date TEXT(25) DEFAULT '1900-01-01 00:00:00', --SQLite3 users text fields to store dates
	deleted INTEGER DEFAULT 0,
	last_change TEXT(25) DEFAULT now()
	PRIMARY KEY (id),
	UNIQUE (name)
);

INSERT INTO princesses(id, name, married, birth_year) VALUES ('1', 'Jasmine', '0', '0200-05-05 13:14:15');
INSERT INTO princesses(id, name, married, birth_year) VALUES ('2', 'Ariel', '0', '1825-06-05 13:00:10');
INSERT INTO princesses(id, name, married, birth_year) VALUES ('3', 'Nala', '0', '1532-05-12 09:10:10');
INSERT INTO princesses(id, name, married, birth_year) VALUES ('4', 'Belle', '1', '1804-12-23 00:53:30');
INSERT INTO princesses(id, name, married, birth_year) VALUES ('5', 'Mulan', '0', '0386-01-20 22:32:12');
INSERT INTO princesses(id, name, married, birth_year) VALUES ('6', 'Rapunzel', '0', '1299-10-13 20:31:19');
INSERT INTO princesses(id, name, married, birth_year) VALUES ('7', 'Tiana', '1', '1893-09-12 15:25:25');