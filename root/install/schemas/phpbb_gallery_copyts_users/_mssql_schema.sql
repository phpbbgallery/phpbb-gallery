/*

 $Id$

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_copyts_users'
*/
CREATE TABLE [phpbb_gallery_copyts_users] (
	[user_id] [int] DEFAULT (0) NOT NULL ,
	[personal_album_id] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_copyts_users] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_copyts_users] PRIMARY KEY  CLUSTERED 
	(
		[user_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

