<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "../../../../../classes/class.ilIniFile.php";
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

$_GET["client_id"] = str_replace("..", "", $_GET["client_id"]);

$dir = getcwd();
chdir("../../../../../");
include_once "webservice/soap/include/inc.soap_functions.php";
$mobs = ilSoapFunctions::getMobsOfObject($_GET["session_id"]."::".$_GET["client_id"], $_GET["obj_type"].":html", $_GET["obj_id"]);
chdir($dir);
$preview = "";
$arr_tinyMCE_image_files = array();
$request_uri = urldecode(empty($_POST['request_uri'])?(empty($_GET['request_uri'])?'':$_GET['request_uri']):$_POST['request_uri']);
$img = isset($_POST['imglist'])? $_POST['imglist'] : '';
$_root = $installpath;
$errors = array();

// upload images
$uploadedFile = false;
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
	
	$uploadedFile = true;	
}

$tpl = new HTML_Template_ITX();
$tpl->loadTemplatefile("tpl.img_upload.html", true, true);

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
	$tpl->setVariable("INSERT_TYPE", "button");
	$tpl->setVariable("INSERT_COMMAND", "{#update}");
	$tpl->touchBlock('src');
	$tpl->touchBlock('preview');
}
else
{
	$tpl->setVariable("INSERT_TYPE", "submit");
	$tpl->setVariable("INSERT_COMMAND", "{#ilimgupload.upload}");
	$tpl->touchBlock('upload');
}

$error_messages = "";
if(!empty($errors))
{
	$error_messages .= '<span class="error">';
	foreach ($errors as $err)
  	{
		$error_messages .= $err . "<br />";
	}
  	$error_messages .= "</span>";
}
else if($uploadedFile)
{
	$tpl->setVariable('WIDTH', $_POST['width']);
	$tpl->setVariable('HEIGHT', $_POST['height']);
	$tpl->setVariable('SRC', $preview);
	$tpl->setVariable('ALT', $_POST['alt']);
	$tpl->touchBlock('adoptimage');
}


$tpl->setVariable("ERROR_MESSAGES", $error_messages);

$tpl->show();

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
?>