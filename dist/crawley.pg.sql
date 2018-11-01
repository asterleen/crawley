--
--       Crawley the Telegram Beholder
--    by Asterleen ~ https://asterleen.com
--
--    https://github.com/asterleen/crawley
--

-- It's better to use this "polyfill" from MySQL than include different
-- implementations in the Crawley's code
CREATE OR REPLACE FUNCTION unix_timestamp(ts timestamp without time zone)
  RETURNS integer AS
$BODY$
begin
    return extract(epoch from ts at time zone 'msk'); -- set your server's timezone here
end
$BODY$
  LANGUAGE plpgsql IMMUTABLE
COST 100;

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

CREATE TABLE post (
  post_attach integer,
  post_text character varying(4096),
  post_timestamp timestamp without time zone NOT NULL DEFAULT now(),
  post_chat_id bigint NOT NULL,
  post_message_id integer NOT NULL,
  CONSTRAINT pk_external_id PRIMARY KEY (post_chat_id, post_message_id),
  CONSTRAINT post_post_attach_fkey FOREIGN KEY (post_attach)
      REFERENCES attach (attach_id) MATCH SIMPLE
);
