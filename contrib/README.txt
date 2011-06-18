Within the zip-package there are several files.
In this file I try to document, for what the files are:
	|_ contrib/
	|	|_ plugins/
	|	|		Contains some plugins for the gallery to open the images dynamical,
	|	|		after clicking on a thumbnail. Just open the *.xml files with your browser.
	|	|		You can ignore the modx.prosilver.en.xsl, see notes at the end of this file.
	|	|
	|	|_ update_X_to_Y/
	|	|		Contains some *.xml-s to guide you through the update.
	|	|	|_ contrib/
	|	|	|	|_ de.xml					Update for the german language-package
	|	|	|	|_ subsilver2.xml			Update for subsilver2-based styles
	|	|	|	\_ modx.prosilver.en.xsl	You can ignore the modx.prosilver.en.xsl, see notes at the end of this file.
	|	|	|_ modx.prosilver.en.xsl	You can ignore the modx.prosilver.en.xsl, see notes at the end of this file.
	|	|	\_ update.xml				Contains all changes that need to be applied to your phpbb-files to run the new version.
	|	|
	|	|_ convert.xml
	|	|		Contains instructions, how to upgrade from Smartors Album MOD (phpBB2) or TS Gallery (phpBB3).
	|	|		You also need to do the install.xml for that!
	|	|_ de.xml					Contains instructions for the german language-package
	|	|_ de_subsilver2.xml		Contains instructions for the german language-package on subsilver2-based styles
	|	|_ history.xml				Contains the full change-log of the gallery as well as the contributions (Developers)
	|	|_ install.diff			(Not available in all versions)		This is a normal diff file and can be applied to your forum-root/
	|	|							You do not need to do the install.xml edits manually than. NOTE: you still have to do the DIY-Instructions at the end.
	|	|_ subsilver2.xml			Contains instructions for subsilver2-based styles
	|	|_ subsilver2.diff		(Not available in all versions)		This is a normal diff file and can be applied to your forum-root/
	|	|							You do not need to do the subsilver2.xml edits manually than. NOTE: you still have to do the DIY-Instructions at the end.
	|	\_ modx.prosilver.en.xsl	You can ignore the modx.prosilver.en.xsl, see notes at the end of this file.
	|
	|
	|_ root/
	|		This folder includes all files the gallery needs to run smooth.
	|		In the manual there are instructions which file needs to be copied to which folder.
	|
	|_ install.xml
	|		This files includes all needed changes for your
	|		phpBB-Board, when you only use prosilver and english language.
	|		JUST OPEN IT WITH YOUR BROWSER! (Mozilla Firefox, Safari, Opera, Internet Explorer, etc.)
	|
	|_ license.txt
	|		This file includes a copy of the license.
	|		The license this MOD is released under is:
	|		GNU GENERAL PUBLIC LICENSE
	|
	\_ modx.prosilver.en.xsl
			This file is needed by the install.xml to be viewable for you.
			You can ignore this file.
