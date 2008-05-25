<?php 
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include "../../../../../classes/class.ilIniFile.php";
$file = "../../../../../ilias.ini.php";
$ini = new ilIniFile($file);
$ini->read();
$htdocs=$ini->readVariable("server", "absolute_path") . "/";
$weburl=$ini->readVariable("server", "http_path") . "/";
$installpath=$htdocs;

// directory where tinymce files are located
$iliasMobPath = "data/" . $_GET["client_id"] . "/mobs/";
$iliasAbsolutePath = $htdocs;
$iliasHttpPath = $weburl;
// base url for images
$tinyMCE_base_url = "$weburl";

$tinyMCE_DOC_url = "$installpath";

// image library related config

// allowed extentions for uploaded image files
$tinyMCE_valid_imgs = array('gif', 'jpg', 'jpeg', 'png');

// allow upload in image library
$tinyMCE_upload_allowed = true;

// allow delete in image library
$tinyMCE_img_delete_allowed = false;

?>