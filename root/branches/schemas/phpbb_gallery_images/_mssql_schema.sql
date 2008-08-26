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
	[image_status] [int] DEFAULT (0) NOT NULL ,
	[image_filemissing] [int] DEFAULT (0) NOT NULL ,
	[image_has_exif] [int] DEFAULT (2) NOT NULL ,
	[image_rates] [int] DEFAULT (0) NOT NULL ,
	[image_rate_points] [int] DEFAULT (0) NOT NULL ,
	[image_rate_avg] [int] DEFAULT (0) NOT NULL ,
	[image_comments] [int] DEFAULT (0) NOT NULL ,
	[image_last_comment] [int] DEFAULT (0) NOT NULL ,
	[image_favorited] [int] DEFAULT (0) NOT NULL ,
	[image_reported] [int] DEFAULT (0) NOT NULL 
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



COMMIT
GO

