/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_comments'
*/
CREATE TABLE [phpbb_gallery_comments] (
	[comment_id] [int] IDENTITY (1, 1) NOT NULL ,
	[comment_image_id] [int] NOT NULL ,
	[comment_user_id] [int] DEFAULT (0) NOT NULL ,
	[comment_username] [varchar] (255) DEFAULT ('') NOT NULL ,
	[comment_user_colour] [varchar] (6) DEFAULT ('') NOT NULL ,
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



COMMIT
GO

