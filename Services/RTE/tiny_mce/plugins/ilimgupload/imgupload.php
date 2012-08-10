<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../../../../');

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/**
 * @var $ilIliasIniFile ilIniFile
 */
global $ilIliasIniFile;

$htdocs      = $ilIliasIniFile->readVariable('server', 'absolute_path') . '/';
$weburl      = $ilIliasIniFile->readVariable('server', 'http_path') . '/';
$installpath = $htdocs;

// directory where tinymce files are located
$iliasMobPath      = 'data/' . CLIENT_ID . '/mobs/';
$iliasAbsolutePath = $htdocs;
$iliasHttpPath     = $weburl;
if($iliasHttpPath)
{
	/**
	 * @var $https ilHttps
	 */
	global $https;

	if(strpos($iliasHttpPath, 'https://') === false && $https->isDetected())
	{
		$iliasHttpPath = str_replace('http://', 'https://', $iliasHttpPath);
	}
}

// base url for images
$tinyMCE_base_url = $weburl;
$tinyMCE_DOC_url  = $installpath;

// allowed extentions for uploaded image files
$tinyMCE_valid_imgs = array('gif', 'jpg', 'jpeg', 'png');

// allow upload in image library
$tinyMCE_upload_allowed = true;

// allow delete in image library
$tinyMCE_img_delete_allowed = false;

include_once 'webservice/soap/include/inc.soap_functions.php';
$mobs        = ilSoapFunctions::getMobsOfObject(session_id() . '::' . CLIENT_ID, $_GET['obj_type'] . ':html', (int)$_GET['obj_id']);
$preview     = '';
$mob_details = array();
$img         = isset($_POST['imglist']) ? $_POST['imglist'] : '';
$_root       = $installpath;

// upload images
$uploadedFile = false;
if(isset($_FILES['img_file']['size']) && $_FILES['img_file']['size'] > 0)
{
	include_once 'webservice/soap/include/inc.soap_functions.php';
	$safefilename = preg_replace('/[^a-zA-z0-9_\.]/', '', $_FILES['img_file']['name']);
	$media_object = ilSoapFunctions::saveTempFileAsMediaObject(session_id() . '::' . CLIENT_ID, $safefilename, $_FILES['img_file']['tmp_name']);
	if(file_exists($iliasAbsolutePath . $iliasMobPath . 'mm_' . $media_object->getId() . '/' . $media_object->getTitle()))
	{
		// only save usage if the file was uploaded
		$media_object->_saveUsage($media_object->getId(), $_GET['obj_type'] . ':html', (int)$_GET['obj_id']);
		
		// Append file to array of existings mobs of this context (obj_type and obj_id)
		$mobs[$media_object->getId()] = $media_object->getId();

		$uploadedFile   = $media_object->getId();
		$_GET['update'] = 1;
	}
}
$mob_details = array();
foreach($mobs as $mob)
{
	$mobdir = $iliasAbsolutePath . $iliasMobPath . 'mm_' . $mob . '/';
	$d      = @dir($mobdir);
	if($d)
	{
		$i = 0;
		while(false !== ($entry = $d->read()))
		{
			$ext = strtolower(substr(strrchr($entry, '.'), 1));
			if(is_file($mobdir . $entry) && in_array($ext, $tinyMCE_valid_imgs))
			{
				$mob_details[$uploadedFile]['file_name'] = $entry;
				$mob_details[$uploadedFile]['file_dir']  = $mobdir;
				$mob_details[$uploadedFile]['http_dir']  = $iliasHttpPath . $iliasMobPath . 'mm_' . $mob . '/';
			}
		}
		$d->close();
	}
}

$tpl = new ilTemplate(dirname(__FILE__) . "/tpl.img_upload.html", true, true);

$tpl->setVariable("OBJ_ID", (int)$_GET["obj_id"]);
$tpl->setVariable("OBJ_TYPE", $_GET["obj_type"]);
$tpl->setVariable("VALUE_UPDATE", (int)$_GET["update"]);
$tpl->setVariable("ILIAS_INST_PATH", $iliasHttpPath);
$tpl->setVariable('IS_UPDATE', (int)$_GET["update"]);
if($ilUser->getLanguage() == 'de')
{
	$tpl->touchBlock('validation_engine_lang_de');
}
else
{
	$tpl->touchBlock('validation_engine_lang_default');
}

if($_GET["update"] == 1)
{
	$tpl->setVariable("IMG_FROM_URL_TAB_DESC", "{#ilimgupload.edit_image}");
	$tpl->setVariable("IMG_FROM_URL_DESC", "{#ilimgupload.edit_image_desc}");
	$tpl->setVariable("INSERT_COMMAND", "{#update}");
}
else
{
	$tpl->setVariable("IMG_FROM_URL_TAB_DESC", "{#ilimgupload.upload_image_from_url}");
	$tpl->setVariable("IMG_FROM_URL_DESC", "{#ilimgupload.upload_image_from_url_desc}");
	$tpl->setVariable("INSERT_COMMAND", "{#ilimgupload.insert}");
}

if($uploadedFile && $mob_details[$uploadedFile])
{
	$img_size = getimagesize($mob_details[$uploadedFile]['file_dir'] . $mob_details[$uploadedFile]['file_name']);
	$tpl->setVariable('UPLOADED_FILE_WIDTH', (int)$img_size[0]);
	$tpl->setVariable('UPLOADED_FILE_HEIGHT', (int)$img_size[1]);
	$tpl->setVariable('UPLOADED_FILE_SRC', $mob_details[$uploadedFile]['http_dir'] . $mob_details[$uploadedFile]['file_name']);
}

$tpl->show();