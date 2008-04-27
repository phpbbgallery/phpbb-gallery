/*

 $Id$

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_roles'
*/
CREATE TABLE [phpbb_gallery_roles] (
	[role_id] [int] IDENTITY (1, 1) NOT NULL ,
	[i_view] [int] DEFAULT (0) NOT NULL ,
	[i_upload] [int] DEFAULT (0) NOT NULL ,
	[i_edit] [int] DEFAULT (0) NOT NULL ,
	[i_delete] [int] DEFAULT (0) NOT NULL ,
	[i_rate] [int] DEFAULT (0) NOT NULL ,
	[i_approve] [int] DEFAULT (0) NOT NULL ,
	[i_lock] [int] DEFAULT (0) NOT NULL ,
	[i_report] [int] DEFAULT (0) NOT NULL ,
	[i_count] [int] DEFAULT (0) NOT NULL ,
	[c_post] [int] DEFAULT (0) NOT NULL ,
	[c_edit] [int] DEFAULT (0) NOT NULL ,
	[c_delete] [int] DEFAULT (0) NOT NULL ,
	[a_moderate] [int] DEFAULT (0) NOT NULL ,
	[album_count] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_roles] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_roles] PRIMARY KEY  CLUSTERED 
	(
		[role_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

