<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../../../../');

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/**
 * @var $ilIliasIniFile ilIniFile
 * @var $lng ilLanguage
 * @var $ilUser ilObjUser
 * @var $https ilHttps
 */
global $ilIliasIniFile, $lng, $ilUser, $https;

$lng->loadLanguageModule("form");

$htdocs = $ilIliasIniFile->readVariable('server', 'absolute_path') . '/';
$weburl = $ilIliasIniFile->readVariable('server', 'absolute_path') . '/';
if(defined('ILIAS_HTTP_PATH'))
{
	$weburl = substr(ILIAS_HTTP_PATH, 0, strrpos(ILIAS_HTTP_PATH, '/Services')) . '/';
}

$installpath = $htdocs;

// directory where tinymce files are located
$iliasMobPath      = 'data/' . CLIENT_ID . '/mobs/';
$iliasAbsolutePath = $htdocs;
$iliasHttpPath     = $weburl;

// base url for images
$tinyMCE_base_url = $weburl;
$tinyMCE_DOC_url  = $installpath;

// allowed extentions for uploaded image files
$tinyMCE_valid_imgs = array('gif', 'jpg', 'jpeg', 'png');

// allow upload in image library
$tinyMCE_upload_allowed = true;

// allow delete in image library
$tinyMCE_img_delete_allowed = false;

$errors = new stdClass();
$errors->general = array();
$errors->fields = array();

include_once 'webservice/soap/include/inc.soap_functions.php';
$mobs        = ilSoapFunctions::getMobsOfObject(session_id() . '::' . CLIENT_ID, $_GET['obj_type'] . ':html', (int)$_GET['obj_id']);
$preview     = '';
$mob_details = array();
$img         = isset($_POST['imglist']) ? $_POST['imglist'] : '';
$_root       = $installpath;

// upload images
$uploadedFile = false;
if(isset($_FILES['img_file']) && is_array($_FILES['img_file']))
{
	// remove trailing '/'
	while(substr($_FILES['img_file']['name'], -1) == '/')
	{
		$_FILES['img_file']['name'] = substr($_FILES['img_file']['name'], 0, -1);
	}

	$error = $_FILES['img_file']['error'];
	switch ($error)
	{
		case UPLOAD_ERR_INI_SIZE:
			$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt('form_msg_file_size_exceeds'));
			break;

		case UPLOAD_ERR_FORM_SIZE:
			$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt("form_msg_file_size_exceeds"));
			break;

		case UPLOAD_ERR_PARTIAL:
			$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt("form_msg_file_partially_uploaded"));
			break;

		case UPLOAD_ERR_NO_FILE:
			$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt("form_msg_file_no_upload"));
			break;

		case UPLOAD_ERR_NO_TMP_DIR:
			$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt("form_msg_file_missing_tmp_dir"));
			break;

		case UPLOAD_ERR_CANT_WRITE:
			$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt("form_msg_file_cannot_write_to_disk"));
			break;

		case UPLOAD_ERR_EXTENSION:
			$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt("form_msg_file_upload_stopped_ext"));
			break;
	}
	
	// check suffixes
	if(!$errors->fields && !$errors->general)
	{
		$finfo = pathinfo($_FILES['img_file']['name']);
		require_once 'Services/Utilities/classes/class.ilMimeTypeUtil.php';
		$mime_type = ilMimeTypeUtil::getMimeType($_FILES['img_file']['tmp_name'], $_FILES['img_file']['name'], $_FILES['img_file']['type']);
		if(!in_array(strtolower($finfo['extension']), $tinyMCE_valid_imgs) || !in_array($mime_type, array(
			'image/gif',
			'image/jpeg',
			'image/png'
		)))
		{
			$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt("form_msg_file_wrong_file_type"));
		}
	}

	// virus handling
	if(!$errors->fields && !$errors->general)
	{
		if($_FILES['img_file']["tmp_name"] != "")
		{
			$vir = ilUtil::virusHandling($_FILES['img_file']["tmp_name"], $_FILES['img_file']["name"]);
			if($vir[0] == false)
			{
				$errors->fields[] = array('name' => 'img_file', 'message' => $lng->txt("form_msg_file_virus_found")."<br />".$vir[1]);
			}
		}
	}

	if(!$errors->fields && !$errors->general)
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
}

$tpl = new ilTemplate(dirname(__FILE__) . "/tpl.img_upload.html", true, true);

$tpl->setVariable("OBJ_ID", (int)$_GET["obj_id"]);
$tpl->setVariable("OBJ_TYPE", $_GET["obj_type"]);
$tpl->setVariable("VALUE_UPDATE", (int)$_GET["update"]);
$tpl->setVariable("ILIAS_INST_PATH", $iliasHttpPath);
$tpl->setVariable("TXT_MAX_SIZE", ilUtil::getFileSizeInfo());
$tpl->setVariable(
	"TXT_ALLOWED_FILE_EXTENSIONS",
	$lng->txt("file_allowed_suffixes")." ".
	implode(', ', array_map(create_function('$value', 'return ".".$value;'), $tinyMCE_valid_imgs))
);

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
	$tpl->setVariable("INSERT_COMMAND", "{#ilimgupload.insert}");
}
else
{
	$tpl->setVariable("IMG_FROM_URL_TAB_DESC", "{#ilimgupload.upload_image_from_url}");
	$tpl->setVariable("IMG_FROM_URL_DESC", "{#ilimgupload.upload_image_from_url_desc}");
	$tpl->setVariable("INSERT_COMMAND", "{#ilimgupload.insert}");
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
if($errors->fields || $errors->general)
{
	foreach($errors->fields as $field)
	{
		$tpl->setCurrentBlock('errors');
		$tpl->setVariable('ERRORS_FIELDNAME', $field['name']);
		$tpl->setVariable('ERRORS_MESSAGE', $field['message']);
		$tpl->parseCurrentBlock();
	}
}
else if($uploadedFile && $mob_details[$uploadedFile])
{
	$img_size = getimagesize($mob_details[$uploadedFile]['file_dir'] . $mob_details[$uploadedFile]['file_name']);
	$tpl->setVariable('UPLOADED_FILE_WIDTH', (int)$img_size[0]);
	$tpl->setVariable('UPLOADED_FILE_HEIGHT', (int)$img_size[1]);
	$tpl->setVariable('UPLOADED_FILE_SRC', $mob_details[$uploadedFile]['http_dir'] . $mob_details[$uploadedFile]['file_name']);
}

$tpl->show();