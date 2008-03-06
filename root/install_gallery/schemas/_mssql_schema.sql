/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_images'
*/
CREATE TABLE [phpbb_gallery_images] (
	[image_id] [int] IDENTITY (1, 1) NOT NULL ,
	[image_filename] [varchar] (255) DEFAULT ('') NOT NULL ,
	[image_thumbnail] [varchar] (255) DEFAULT ('') NOT NULL ,
	[image_name] [varchar] (255) DEFAULT ('') NOT NULL ,
	[image_desc] [text] DEFAULT ('') NOT NULL ,
	[image_desc_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[image_desc_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[image_user_id] [int] DEFAULT (0) NOT NULL ,
	[image_username] [varchar] (255) DEFAULT ('') NOT NULL ,
	[image_user_colour] [varchar] (6) DEFAULT ('') NOT NULL ,
	[image_user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[image_time] [int] DEFAULT (0) NOT NULL ,
	[image_album_id] [int] DEFAULT (0) NOT NULL ,
	[image_view_count] [int] DEFAULT (0) NOT NULL ,
	[image_lock] [int] DEFAULT (0) NOT NULL ,
	[image_approval] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_images] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_images] PRIMARY KEY  CLUSTERED 
	(
		[image_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [image_album_id] ON [phpbb_gallery_images]([image_album_id]) ON [PRIMARY]
GO

CREATE  INDEX [image_user_id] ON [phpbb_gallery_images]([image_user_id]) ON [PRIMARY]
GO

CREATE  INDEX [image_time] ON [phpbb_gallery_images]([image_time]) ON [PRIMARY]
GO


/*
	Table: 'phpbb_gallery_albums'
*/
CREATE TABLE [phpbb_gallery_albums] (
	[album_id] [int] IDENTITY (1, 1) NOT NULL ,
	[parent_id] [int] DEFAULT (0) NOT NULL ,
	[left_id] [int] DEFAULT (1) NOT NULL ,
	[right_id] [int] DEFAULT (2) NOT NULL ,
	[album_parents] [text] DEFAULT ('') NOT NULL ,
	[album_type] [int] DEFAULT (1) NOT NULL ,
	[album_name] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_desc] [text] DEFAULT ('') NOT NULL ,
	[album_desc_options] [int] DEFAULT (7) NOT NULL ,
	[album_desc_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[album_desc_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_user_id] [int] DEFAULT (0) NOT NULL ,
	[album_order] [int] DEFAULT (0) NOT NULL ,
	[album_view_level] [int] DEFAULT (1) NOT NULL ,
	[album_upload_level] [int] DEFAULT (0) NOT NULL ,
	[album_rate_level] [int] DEFAULT (0) NOT NULL ,
	[album_comment_level] [int] DEFAULT (0) NOT NULL ,
	[album_edit_level] [int] DEFAULT (0) NOT NULL ,
	[album_delete_level] [int] DEFAULT (2) NOT NULL ,
	[album_view_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_upload_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_rate_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_comment_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_edit_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_delete_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_moderator_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_approval] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_albums] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_albums] PRIMARY KEY  CLUSTERED 
	(
		[album_id]
	)  ON [PRIMARY] 
GO


/*
	Table: 'phpbb_gallery_comments'
*/
CREATE TABLE [phpbb_gallery_comments] (
	[comment_id] [int] IDENTITY (1, 1) NOT NULL ,
	[comment_image_id] [int] NOT NULL ,
	[comment_user_id] [int] DEFAULT (0) NOT NULL ,
	[comment_username] [varchar] (32) DEFAULT ('') NOT NULL ,
	[comment_user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[comment_time] [int] DEFAULT (0) NOT NULL ,
	[comment] [text] DEFAULT ('') NOT NULL ,
	[comment_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[comment_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[comment_edit_time] [int] DEFAULT (0) NOT NULL ,
	[comment_edit_count] [int] DEFAULT (0) NOT NULL ,
	[comment_edit_user_id] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_comments] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_comments] PRIMARY KEY  CLUSTERED 
	(
		[comment_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [comment_image_id] ON [phpbb_gallery_comments]([comment_image_id]) ON [PRIMARY]
GO

CREATE  INDEX [comment_user_id] ON [phpbb_gallery_comments]([comment_user_id]) ON [PRIMARY]
GO

CREATE  INDEX [comment_user_ip] ON [phpbb_gallery_comments]([comment_user_ip]) ON [PRIMARY]
GO

CREATE  INDEX [comment_time] ON [phpbb_gallery_comments]([comment_time]) ON [PRIMARY]
GO


/*
	Table: 'phpbb_gallery_config'
*/
CREATE TABLE [phpbb_gallery_config] (
	[config_name] [varchar] (255) DEFAULT ('') NOT NULL ,
	[config_value] [varchar] (255) DEFAULT ('') NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_config] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_config] PRIMARY KEY  CLUSTERED 
	(
		[config_name]
	)  ON [PRIMARY] 
GO


/*
	Table: 'phpbb_gallery_rates'
*/
CREATE TABLE [phpbb_gallery_rates] (
	[rate_image_id] [int] IDENTITY (1, 1) NOT NULL ,
	[rate_user_id] [int] DEFAULT (0) NOT NULL ,
	[rate_user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[rate_point] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

CREATE  INDEX [rate_image_id] ON [phpbb_gallery_rates]([rate_image_id]) ON [PRIMARY]
GO

CREATE  INDEX [rate_user_id] ON [phpbb_gallery_rates]([rate_user_id]) ON [PRIMARY]
GO

CREATE  INDEX [rate_user_ip] ON [phpbb_gallery_rates]([rate_user_ip]) ON [PRIMARY]
GO

CREATE  INDEX [rate_point] ON [phpbb_gallery_rates]([rate_point]) ON [PRIMARY]
GO



COMMIT
GO

