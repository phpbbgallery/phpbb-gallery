#
# $Id$
#

# Table: 'phpbb_gallery_config'
CREATE TABLE phpbb_gallery_config (
	config_name varbinary(255) DEFAULT '' NOT NULL,
	config_value varbinary(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (config_name)
);


