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

include_once "HTML/Template/ITX.php";
include_once "config.php"; 

$dir = getcwd();
chdir("../../../../../");
include_once "webservice/soap/include/inc.soap_functions.php";
$mobs = ilSoapFunctions::getMobsOfObject($_GET["session_id"]."::".$_GET["client_id"], $_GET["obj_type"].":html", $_GET["obj_id"]);
chdir($dir);

$preview = "";
$arr_tinyMCE_image_files = array();
$request_uri = urldecode(empty($_POST['request_uri'])?(empty($_GET['request_uri'])?'':$_GET['request_uri']):$_POST['request_uri']);
$img = isset($_POST['imglist'])?$_POST['imglist']:'';
$_root = $installpath;
$errors = array();

// upload images
if (isset($_FILES['img_file']['size']) && $_FILES['img_file']['size']>0)
{
	$dir = getcwd();
	chdir("../../../../../");
	include_once "webservice/soap/include/inc.soap_functions.php";
	$safefilename = preg_replace("/[^a-zA-z0-9_\.]/", "", $_FILES["img_file"]["name"]);
	$media_object = ilSoapFunctions::saveTempFileAsMediaObject($_GET["session_id"]."::".$_GET["client_id"], $safefilename, $_FILES["img_file"]["tmp_name"]);
	if (file_exists($iliasAbsolutePath.$iliasMobPath."mm_".$media_object->getId() . "/" . $media_object->getTitle()))
	{
		// only save usage if the file was uploaded
		$media_object->_saveUsage($media_object->getId(), $_GET["obj_type"].":html", $_GET["obj_id"]);
	}
	chdir($dir);
	$preview = $iliasHttpPath.$iliasMobPath."mm_".$media_object->getId() . "/" . $media_object->getTitle();
	$mobs[$media_object->getId()] = $media_object->getId();
}

$tpl = new HTML_Template_ITX();
$tpl->loadTemplatefile("tpl.imagemanager.html", TRUE, TRUE);

// delete image
if ($tinyMCE_img_delete_allowed && isset($_POST['lib_action']) 
	&& ($_POST['lib_action']=='delete') && !empty($img)) 
{
  deleteImg();
}

if ($tinyMCE_img_delete_allowed)
{
	$tpl->touchBlock("delete_allowed");
}
outMobImages();
outMobImageParams();
$tpl->setVariable("REQUEST_URI", $_GET["request_uri"]);
$tpl->setVariable("VALUE_REQUEST_URI", $request_uri);
$tpl->setVariable("OBJ_ID", $_GET["obj_id"]);
$tpl->setVariable("OBJ_TYPE", $_GET["obj_type"]);
$tpl->setVariable("SESSION_ID", $_GET["session_id"]);
$tpl->setVariable("CLIENT_ID", $_GET["client_id"]);
$tpl->setVariable("VALUE_UPDATE", $_GET["update"]);
$tpl->setVariable("ILIAS_INST_PATH", $iliasHttpPath);
if ($_GET["update"] == 1)
{
	$tpl->setVariable("INSERT_COMMAND", "{#update}");
}
else
{
	$tpl->setVariable("INSERT_COMMAND", "{#insert}");
}
$tpl->setVariable("URL_PREVIEW", $preview);
$error_messages = "";
if (!empty($errors))
{
	$error_messages .= '<span class="error">';
  foreach ($errors as $err)
  {
		$error_messages .= $err . "<br />";
	}
  $error_messages .= "</span>";
}
$tpl->setVariable("ERROR_MESSAGES", $error_messages);
$tpl->show();

	function outMobImages()
	{
		global $mobs;
		global $iliasMobPath;
		global $iliasAbsolutePath;
		global $iliasHttpPath;
		global $tinyMCE_valid_imgs;
		global $tpl;
		global $errors;
		global $img;
		global $arr_tinyMCE_image_files;
		
		$i = 0;
		// read image directory
		foreach ($mobs as $mob)
		{
			$mobdir = $iliasAbsolutePath.$iliasMobPath."mm_".$mob . "/";
			$d = @dir($mobdir);
			if ($d) 
			{
				while (FALSE !== ($entry = $d->read())) 
				{
					$ext = strtolower(substr(strrchr($entry,'.'), 1));
					if (is_file($mobdir.$entry) && in_array($ext, $tinyMCE_valid_imgs))
					{
						$arr_tinyMCE_image_files[$i]["file_name"] = $entry;			
						$arr_tinyMCE_image_files[$i]["file_dir"] = $mobdir;			
						$arr_tinyMCE_image_files[$i]["http_dir"] = $iliasHttpPath.$iliasMobPath."mm_".$mob . "/";			
						$i++;
					}
				}
				$d->close();
			}  
			else
			{
				$errors[] = '{#ibrowser.errornodir}';
			}
		}
		// sort the list of image filenames alphabetically.
		sort($arr_tinyMCE_image_files);
		
		for ($k=0; $k<count($arr_tinyMCE_image_files); $k++)
		{ 
			$entry = $arr_tinyMCE_image_files[$k]["file_name"];
			$size = getimagesize($arr_tinyMCE_image_files[$k]["file_dir"].$entry);
			$fsize = filesize($arr_tinyMCE_image_files[$k]["file_dir"].$entry);
			$tpl->setCurrentBlock("imagefile");
			$tpl->setVariable("IMAGEFILE_VALUE", $arr_tinyMCE_image_files[$k]["http_dir"]);
			$tpl->setVariable("IMAGEFILE_TEXT", $entry);
			if ($entry == $img)
			{
				$tpl->setVariable("IMAGEFILE_SELECTED", " selected=\"selected\"");
			}
			$tpl->parseCurrentBlock();
		}
	}
	
	function outMobImageParams()
	{
		global $arr_tinyMCE_image_files;
		global $tpl;
		for ($k=0; $k<count($arr_tinyMCE_image_files); $k++)
		{
			$tpl->setCurrentBlock("imageparams");
			$entry = $arr_tinyMCE_image_files[$k]["file_name"];
		  $size = getimagesize($arr_tinyMCE_image_files[$k]["file_dir"].$entry);
		  $fsize = filesize($arr_tinyMCE_image_files[$k]["file_dir"].$entry);
			$tpl->setVariable("IMG_WIDTH", $size[0]);
			$tpl->setVariable("IMG_HEIGHT", $size[1]);
			$tpl->setVariable("IMG_PATH", $arr_tinyMCE_image_files[$k]["http_dir"]);
			$tpl->setVariable("F_SIZE", filesize_h($fsize,2));
			$tpl->parseCurrentBlock();
		}
	}

	function deleteImg()
	{
	}

// Return the human readable size of a file
// @param int $size a file size
// @param int $dec a number of decimal places

function filesize_h($size, $dec = 1)
{
	$sizes = array('byte(s)', 'kb', 'mb', 'gb');
	$count = count($sizes);
	$i = 0;

	while ($size >= 1024 && ($i < $count - 1)) {
		$size /= 1024;
		$i++;
	}

	return round($size, $dec) . ' ' . $sizes[$i];
}
	
function liboptions($arr, $prefix = '', $sel = '')
{
  $buf = '';
  foreach($arr as $lib) {
    $buf .= '<option value="'.$lib['value'].'"'.(($lib['value'] == $sel)?' selected':'').'>'.$prefix.$lib['text'].'</option>'."\n";
  }
  return $buf;
}

?>