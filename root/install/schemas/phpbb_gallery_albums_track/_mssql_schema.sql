/*

 $Id$

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_albums_track'
*/
CREATE TABLE [phpbb_gallery_albums_track] (
	[user_id] [int] DEFAULT (0) NOT NULL ,
	[album_id] [int] DEFAULT (0) NOT NULL ,
	[mark_time] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_albums_track] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_albums_track] PRIMARY KEY  CLUSTERED 
	(
		[user_id],
		[album_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

