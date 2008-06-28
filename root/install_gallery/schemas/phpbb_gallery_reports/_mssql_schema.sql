/*

 $Id$

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_reports'
*/
CREATE TABLE [phpbb_gallery_reports] (
	[report_id] [int] IDENTITY (1, 1) NOT NULL ,
	[report_album_id] [int] DEFAULT (0) NOT NULL ,
	[report_image_id] [int] DEFAULT (0) NOT NULL ,
	[reporter_id] [int] DEFAULT (0) NOT NULL ,
	[report_manager] [int] DEFAULT (0) NOT NULL ,
	[report_note] [text] DEFAULT ('') NOT NULL ,
	[report_time] [int] DEFAULT (0) NOT NULL ,
	[report_status] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_reports] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_reports] PRIMARY KEY  CLUSTERED 
	(
		[report_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

