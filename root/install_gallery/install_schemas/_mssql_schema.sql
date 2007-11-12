/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_album'
*/
CREATE TABLE [phpbb_album] (
	[pic_id] [int] IDENTITY (1, 1) NOT NULL ,
	[pic_filename] [varchar] (255) DEFAULT ('') NOT NULL ,
	[pic_thumbnail] [varchar] (255) DEFAULT ('') NOT NULL ,
	[pic_title] [varchar] (255) DEFAULT ('') NOT NULL ,
	[pic_desc] [text] DEFAULT ('') NOT NULL ,
	[pic_desc_bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[pic_desc_bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[pic_user_id] [int] DEFAULT (0) NOT NULL ,
	[pic_username] [varchar] (32) DEFAULT ('') NOT NULL ,
	[pic_user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[pic_time] [int] DEFAULT (0) NOT NULL ,
	[pic_cat_id] [int] DEFAULT (0) NOT NULL ,
	[pic_view_count] [int] DEFAULT (0) NOT NULL ,
	[pic_lock] [int] DEFAULT (0) NOT NULL ,
	[pic_approval] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_album] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_album] PRIMARY KEY  CLUSTERED 
	(
		[pic_id]
	)  ON [PRIMARY] 
GO


/*
	Table: 'phpbb_album_cat'
*/
CREATE TABLE [phpbb_album_cat] (
	[cat_id] [int] IDENTITY (1, 1) NOT NULL ,
	[cat_title] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_desc] [text] DEFAULT ('') NOT NULL ,
	[cat_desc_bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[cat_desc_bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_order] [int] DEFAULT (0) NOT NULL ,
	[cat_view_level] [int] DEFAULT (1) NOT NULL ,
	[cat_upload_level] [int] DEFAULT (0) NOT NULL ,
	[cat_rate_level] [int] DEFAULT (0) NOT NULL ,
	[cat_comment_level] [int] DEFAULT (0) NOT NULL ,
	[cat_edit_level] [int] DEFAULT (0) NOT NULL ,
	[cat_delete_level] [int] DEFAULT (2) NOT NULL ,
	[cat_view_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_upload_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_rate_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_comment_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_edit_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_delete_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_moderator_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[cat_approval] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_album_cat] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_album_cat] PRIMARY KEY  CLUSTERED 
	(
		[cat_id]
	)  ON [PRIMARY] 
GO


/*
	Table: 'phpbb_album_comment'
*/
CREATE TABLE [phpbb_album_comment] (
	[comment_id] [int] IDENTITY (1, 1) NOT NULL ,
	[comment_pic_id] [int] NOT NULL ,
	[comment_user_id] [int] DEFAULT (0) NOT NULL ,
	[comment_username] [varchar] (32) DEFAULT ('') NOT NULL ,
	[comment_user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[comment_time] [int] DEFAULT (0) NOT NULL ,
	[comment_text] [text] DEFAULT ('') NOT NULL ,
	[comment_text_bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[comment_text_bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[comment_edit_time] [int] DEFAULT (0) NOT NULL ,
	[comment_edit_count] [int] DEFAULT (0) NOT NULL ,
	[comment_edit_user_id] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_album_comment] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_album_comment] PRIMARY KEY  CLUSTERED 
	(
		[comment_id]
	)  ON [PRIMARY] 
GO


/*
	Table: 'phpbb_album_config'
*/
CREATE TABLE [phpbb_album_config] (
	[config_name] [varchar] (255) DEFAULT ('') NOT NULL ,
	[config_value] [varchar] (255) DEFAULT ('') NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_album_config] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_album_config] PRIMARY KEY  CLUSTERED 
	(
		[config_name]
	)  ON [PRIMARY] 
GO


/*
	Table: 'phpbb_album_rate'
*/
CREATE TABLE [phpbb_album_rate] (
	[rate_pic_id] [int] IDENTITY (1, 1) NOT NULL ,
	[rate_user_id] [int] DEFAULT (0) NOT NULL ,
	[rate_user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[rate_point] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_album_rate] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_album_rate] PRIMARY KEY  CLUSTERED 
	(
		[rate_pic_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

