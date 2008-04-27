/*

 $Id$

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_permissions'
*/
CREATE TABLE [phpbb_gallery_permissions] (
	[perm_id] [int] IDENTITY (1, 1) NOT NULL ,
	[perm_role_id] [int] DEFAULT (0) NOT NULL ,
	[perm_album_id] [int] DEFAULT (0) NOT NULL ,
	[perm_user_id] [int] DEFAULT (0) NOT NULL ,
	[perm_group_id] [int] DEFAULT (0) NOT NULL ,
	[perm_system] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_permissions] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_permissions] PRIMARY KEY  CLUSTERED 
	(
		[perm_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

