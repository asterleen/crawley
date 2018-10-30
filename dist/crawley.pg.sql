--
--       Crawley the Telegram Beholder
--    by Asterleen ~ https://asterleen.com
--
--    https://github.com/asterleen/crawley
--


CREATE TABLE attach_type (
    attach_type_tag character varying(16) NOT NULL,
    attach_type_description character varying(32) NOT NULL
);

ALTER TABLE ONLY attach_type
    ADD CONSTRAINT attach_type_pkey PRIMARY KEY (attach_type_tag);

INSERT INTO attach_type (attach_type_tag, attach_type_description) VALUES ('photo', 'Photo/Image');
INSERT INTO attach_type (attach_type_tag, attach_type_description) VALUES ('audio', 'Audio file');
INSERT INTO attach_type (attach_type_tag, attach_type_description) VALUES ('voice', 'Voice message');


CREATE SEQUENCE seq_attach_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE attach (
    attach_id integer DEFAULT nextval('seq_attach_id'::regclass) NOT NULL,
    attach_type_tag character varying(16) NOT NULL,
    attach_filename character varying(256) NOT NULL
);


ALTER TABLE ONLY attach
    ADD CONSTRAINT attach_attach_filename_key UNIQUE (attach_filename);

ALTER TABLE ONLY attach
    ADD CONSTRAINT attach_pkey PRIMARY KEY (attach_id);

ALTER TABLE ONLY attach
    ADD CONSTRAINT attach_attach_type_tag_fkey FOREIGN KEY (attach_type_tag) REFERENCES attach_type(attach_type_tag);

CREATE SEQUENCE seq_post_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE post (
    post_id integer DEFAULT nextval('seq_post_id'::regclass) NOT NULL,
    post_attach integer,
    post_text character varying(4096),
    post_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    post_external_id character varying(32) NOT NULL
);

ALTER TABLE ONLY post
    ADD CONSTRAINT pk_post_id PRIMARY KEY (post_id);

ALTER TABLE ONLY post
    ADD CONSTRAINT unique_external_id UNIQUE (post_external_id);

ALTER TABLE ONLY post
    ADD CONSTRAINT post_post_attach_fkey FOREIGN KEY (post_attach) REFERENCES attach(attach_id);
