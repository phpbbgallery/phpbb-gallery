/*

 $Id$

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_contests'
*/
CREATE TABLE [phpbb_gallery_contests] (
	[contest_id] [int] IDENTITY (1, 1) NOT NULL ,
	[contest_album_id] [int] DEFAULT (0) NOT NULL ,
	[contest_start] [int] DEFAULT (0) NOT NULL ,
	[contest_rating] [int] DEFAULT (0) NOT NULL ,
	[contest_end] [int] DEFAULT (0) NOT NULL ,
	[contest_marked] [int] DEFAULT (0) NOT NULL ,
	[contest_first] [int] DEFAULT (0) NOT NULL ,
	[contest_second] [int] DEFAULT (0) NOT NULL ,
	[contest_third] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_gallery_contests] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_gallery_contests] PRIMARY KEY  CLUSTERED 
	(
		[contest_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

