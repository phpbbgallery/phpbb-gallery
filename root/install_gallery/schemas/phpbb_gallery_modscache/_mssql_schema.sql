/*

 $Id$

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_modscache'
*/
CREATE TABLE [phpbb_gallery_modscache] (
	[album_id] [int] DEFAULT (0) NOT NULL ,
	[user_id] [int] DEFAULT (0) NOT NULL ,
	[username] [varchar] (255) DEFAULT ('') NOT NULL ,
	[group_id] [int] DEFAULT (0) NOT NULL ,
	[group_name] [varchar] (255) DEFAULT ('') NOT NULL ,
	[display_on_index] [int] DEFAULT (1) NOT NULL 
) ON [PRIMARY]
GO

CREATE  INDEX [disp_idx] ON [phpbb_gallery_modscache]([display_on_index]) ON [PRIMARY]
GO

CREATE  INDEX [album_id] ON [phpbb_gallery_modscache]([album_id]) ON [PRIMARY]
GO



COMMIT
GO

