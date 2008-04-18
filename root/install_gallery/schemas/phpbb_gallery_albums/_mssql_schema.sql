/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_albums'
*/
CREATE TABLE [phpbb_gallery_albums] (
	[album_id] [int] IDENTITY (1, 1) NOT NULL ,
	[parent_id] [int] DEFAULT (0) NOT NULL ,
	[left_id] [int] DEFAULT (1) NOT NULL ,
	[right_id] [int] DEFAULT (2) NOT NULL ,
	[album_parents] [text] DEFAULT ('') NOT NULL ,
	[album_type] [int] DEFAULT (1) NOT NULL ,
	[album_name] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_desc] [text] DEFAULT ('') NOT NULL ,
	[album_desc_options] [int] DEFAULT (7) NOT NULL ,
	[album_desc_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[album_desc_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_user_id] [int] DEFAULT (0) NOT NULL ,
	[album_order] [int] DEFAULT (0) NOT NULL ,
	[album_view_level] [int] DEFAULT (1) NOT NULL ,
	[album_upload_level] [int] DEFAULT (0) NOT NULL ,
	[album_rate_level] [int] DEFAULT (0) NOT NULL ,
	[album_comment_level] [int] DEFAULT (0) NOT NULL ,
	[album_edit_level] [int] DEFAULT (0) NOT NULL ,
	[album_delete_level] [int] DEFAULT (2) NOT NULL ,
	[album_view_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_upload_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_rate_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_comment_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_edit_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_delete_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_moderator_groups] [varchar] (255) DEFAULT ('') NOT NULL ,
	[album_approval] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_albums] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_albums] PRIMARY KEY  CLUSTERED 
	(
		[album_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

