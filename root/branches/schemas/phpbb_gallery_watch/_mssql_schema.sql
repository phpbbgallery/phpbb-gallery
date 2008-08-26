/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_watch'
*/
CREATE TABLE [phpbb_gallery_watch] (
	[watch_id] [int] IDENTITY (1, 1) NOT NULL ,
	[album_id] [int] DEFAULT (0) NOT NULL ,
	[image_id] [int] DEFAULT (0) NOT NULL ,
	[user_id] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_watch] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_watch] PRIMARY KEY  CLUSTERED 
	(
		[watch_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [user_id] ON [phpbb_gallery_watch]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [image_id] ON [phpbb_gallery_watch]([image_id]) ON [PRIMARY]
GO

CREATE  INDEX [album_id] ON [phpbb_gallery_watch]([album_id]) ON [PRIMARY]
GO



COMMIT
GO

