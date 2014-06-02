
CREATE TABLE users (
    	username varchar(255) NOT NULL,
	role varchar(10) NOT NULL,
   	primary key (username) 
);

CREATE TABLE roles (        
	role varchar(10) NOT NULL,
   	primary key (role) 
);

CREATE TABLE extensions ( 
	id BIGSERIAL NOT NULL,      
	extension_number varchar(20) NOT NULL,
	primary_number boolean default false,
	username varchar(255) NOT NULL,
	pin varchar(20),
	useredit boolean default true,
   	primary key (id)
);

CREATE TABLE history ( 
	id BIGSERIAL NOT NULL,      
	dialled_party varchar(20) NOT NULL,
	date_from timestamp NOT NULL,
	date_to timestamp,
	username varchar(255) NOT NULL,
   	primary key (id)
);

INSERT INTO roles values ('admin');
INSERT INTO roles values ('user');

INSERT INTO users values ('admin','admin');
INSERT INTO users values ('ABC01234','user');
INSERT INTO users values ('ABC05678','user');

INSERT INTO extensions (extension_number, primary_number, username, pin) values ('220','true','admin','1234');
INSERT INTO extensions (extension_number, primary_number, username, pin) values ('210','true','ABC01234','1234');
INSERT INTO extensions (extension_number, primary_number, username, pin) values ('220','true','ABC01234','1234');
INSERT INTO extensions (extension_number, primary_number, username, pin) values ('230','true','ABC05678','1234');

ALTER TABLE users add constraint fk_user_role FOREIGN KEY (role) REFERENCES roles;
ALTER TABLE users add constraint fk_extension_user FOREIGN KEY (username) REFERENCES users;
ALTER TABLE users add constraint fk_history_user FOREIGN KEY (username) REFERENCES users;

CREATE USER ctiuser WITH PASSWORD 'ctiftw01';
GRANT ALL PRIVILEGES ON DATABASE ctidb TO ctiuser;
GRANT ALL PRIVILEGES ON TABLE users TO ctiuser;
GRANT ALL PRIVILEGES ON TABLE roles TO ctiuser;
GRANT ALL PRIVILEGES ON TABLE extensions TO ctiuser;
GRANT ALL PRIVILEGES ON TABLE history TO ctiuser;
GRANT ALL PRIVILEGES ON TABLE extensions_id_seq TO ctiuser;
GRANT ALL PRIVILEGES ON TABLE history_id_seq TO ctiuser;
