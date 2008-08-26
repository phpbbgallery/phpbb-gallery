/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_favorites'
*/
CREATE TABLE [phpbb_gallery_favorites] (
	[favorite_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] DEFAULT (0) NOT NULL ,
	[image_id] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_favorites] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_favorites] PRIMARY KEY  CLUSTERED 
	(
		[favorite_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [user_id] ON [phpbb_gallery_favorites]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [image_id] ON [phpbb_gallery_favorites]([image_id]) ON [PRIMARY]
GO



COMMIT
GO

