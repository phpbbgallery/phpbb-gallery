/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_gallery_rates'
*/
CREATE TABLE [phpbb_gallery_rates] (
	[rate_image_id] [int] IDENTITY (1, 1) NOT NULL ,
	[rate_user_id] [int] DEFAULT (0) NOT NULL ,
	[rate_user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[rate_point] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

CREATE  INDEX [rate_image_id] ON [phpbb_gallery_rates]([rate_image_id]) ON [PRIMARY]
GO

CREATE  INDEX [rate_user_id] ON [phpbb_gallery_rates]([rate_user_id]) ON [PRIMARY]
GO

CREATE  INDEX [rate_user_ip] ON [phpbb_gallery_rates]([rate_user_ip]) ON [PRIMARY]
GO

CREATE  INDEX [rate_point] ON [phpbb_gallery_rates]([rate_point]) ON [PRIMARY]
GO



COMMIT
GO

