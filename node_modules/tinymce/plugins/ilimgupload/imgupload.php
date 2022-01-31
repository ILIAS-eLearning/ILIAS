<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use ILIAS\HTTP\Response\ResponseHeader;

chdir('../../../../');

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/**
 * @var ilIniFile $ilIliasIniFile
 * @var ilLanguage $lng
 * @var ilObjUser $ilUser
 * @var ilHttps $https
 */
global $DIC;

$ilIliasIniFile = $DIC['ilIliasIniFile'];
$lng = $DIC['lng'];
$ilUser = $DIC['ilUser'];
$https = $DIC['https'];

$lng->loadLanguageModule('form');

$htdocs = $ilIliasIniFile->readVariable('server', 'absolute_path') . '/';
$weburl = $ilIliasIniFile->readVariable('server', 'absolute_path') . '/';
if (defined('ILIAS_HTTP_PATH')) {
    $weburl = substr(ILIAS_HTTP_PATH, 0, strrpos(ILIAS_HTTP_PATH, '/node_modules')) . '/';
}

$installpath = $htdocs;

// directory where tinymce files are located
$iliasMobPath = 'data/' . CLIENT_ID . '/mobs/';
$iliasAbsolutePath = $htdocs;
$iliasHttpPath = $weburl;

// base url for images
$tinyMCE_base_url = $weburl;
$tinyMCE_DOC_url = $installpath;

// allowed extentions for uploaded image files
$tinyMCE_valid_imgs = ['gif', 'jpg', 'jpeg', 'png'];

// allow upload in image library
$tinyMCE_upload_allowed = true;

// allow delete in image library
$tinyMCE_img_delete_allowed = false;

$errors = new stdClass();
$errors->general = [];
$errors->fields = [];

include_once 'webservice/soap/include/inc.soap_functions.php';
$mobs = ilSoapFunctions::getMobsOfObject(
    session_id() . '::' . CLIENT_ID,
    $DIC->http()->wrapper()->query()->retrieve(
        'obj_type',
        $DIC->refinery()->kindlyTo()->string()
    ) . ':html',
    $DIC->http()->wrapper()->query()->retrieve(
        'obj_id',
        $DIC->refinery()->kindlyTo()->int()
    )
);
$preview = '';
$mob_details = [];
$img = '';
if ($DIC->http()->wrapper()->post()->has('imglist')) {
    $img = $DIC->http()->wrapper()->post()->retrieve(
        'imglist',
        $DIC->refinery()->kindlyTo()->string()
    );
}
$_root = $installpath;

$update = false;
if ($DIC->http()->wrapper()->query()->has('update')) {
    $update = $DIC->http()->wrapper()->query()->retrieve(
        'update',
        $DIC->refinery()->kindlyTo()->bool()
    );
}

// upload images
$uploadedFile = false;
if (isset($_FILES['img_file']) && is_array($_FILES['img_file'])) {
    while (substr($_FILES['img_file']['name'], -1) === '/') {
        $_FILES['img_file']['name'] = substr($_FILES['img_file']['name'], 0, -1);
    }

    $error = $_FILES['img_file']['error'];
    switch ($error) {
        case UPLOAD_ERR_INI_SIZE:
            $errors->fields[] = ['name' => 'img_file', 'message' => $lng->txt('form_msg_file_size_exceeds')];
            break;

        case UPLOAD_ERR_FORM_SIZE:
            $errors->fields[] = ['name' => 'img_file', 'message' => $lng->txt("form_msg_file_size_exceeds")];
            break;

        case UPLOAD_ERR_PARTIAL:
            $errors->fields[] = ['name' => 'img_file', 'message' => $lng->txt("form_msg_file_partially_uploaded")];
            break;

        case UPLOAD_ERR_NO_FILE:
            $errors->fields[] = ['name' => 'img_file', 'message' => $lng->txt("form_msg_file_no_upload")];
            break;

        case UPLOAD_ERR_NO_TMP_DIR:
            $errors->fields[] = ['name' => 'img_file', 'message' => $lng->txt("form_msg_file_missing_tmp_dir")];
            break;

        case UPLOAD_ERR_CANT_WRITE:
            $errors->fields[] = ['name' => 'img_file', 'message' => $lng->txt("form_msg_file_cannot_write_to_disk")];
            break;

        case UPLOAD_ERR_EXTENSION:
            $errors->fields[] = ['name' => 'img_file', 'message' => $lng->txt("form_msg_file_upload_stopped_ext")];
            break;
    }

    // check suffixes
    if (!$errors->fields && !$errors->general) {
        $finfo = pathinfo($_FILES['img_file']['name']);
        $mime_type = ilMimeTypeUtil::getMimeType(
            $_FILES['img_file']['tmp_name'],
            $_FILES['img_file']['name'],
            $_FILES['img_file']['type']
        );
        if (
            !in_array($mime_type, ['image/gif', 'image/jpeg', 'image/png'], true) ||
            !in_array(strtolower($finfo['extension']), $tinyMCE_valid_imgs, true)
        ) {
            $errors->fields[] = ['name' => 'img_file', 'message' => $lng->txt("form_msg_file_wrong_file_type")];
        }
    }

    // virus handling
    if (
        !$errors->fields &&
        !$errors->general &&
        $_FILES['img_file']['tmp_name'] !== ''
    ) {
        $vir = ilUtil::virusHandling($_FILES['img_file']['tmp_name'], $_FILES['img_file']['name']);
        if ($vir[0] === false) {
            $errors->fields[] = [
                'name' => 'img_file',
                'message' => $lng->txt('form_msg_file_virus_found') . '<br />' . $vir[1]
            ];
        }
    }
    if (!$errors->fields && !$errors->general) {
        $safefilename = preg_replace('/[^a-zA-Z0-9_\.]/', '', $_FILES['img_file']['name']);
        $media_object = ilSoapFunctions::saveTempFileAsMediaObject(
            session_id() . '::' . CLIENT_ID,
            $safefilename,
            $_FILES['img_file']['tmp_name']
        );
        if (file_exists($iliasAbsolutePath . $iliasMobPath . 'mm_' . $media_object->getId() . '/' . $media_object->getTitle())) {
            // only save usage if the file was uploaded
            $media_object::_saveUsage(
                $media_object->getId(),
                $DIC->http()->wrapper()->query()->retrieve(
                    'obj_type',
                    $DIC->refinery()->kindlyTo()->string()
                ) . ':html',
                $DIC->http()->wrapper()->query()->retrieve(
                    'obj_id',
                    $DIC->refinery()->kindlyTo()->int()
                )
            );

            // Append file to array of existings mobs of this context (obj_type and obj_id)
            $mobs[$media_object->getId()] = $media_object->getId();

            $uploadedFile = $media_object->getId();
            $update = true;
        }
    }
}

$panel = ['img_insert_command' => "ilimgupload.insert"];
if ($update) {
    $panel["img_url_tab_desc"] = "ilimgupload.edit_image";
    $panel["img_from_url_desc"] = "ilimgupload.edit_image_desc";
} else {
    $panel["img_url_tab_desc"] = "ilimgupload.upload_image_from_url";
    $panel["img_from_url_desc"] = "ilimgupload.upload_image_from_url_desc";
}

$mob_details = [];
foreach ($mobs as $mob) {
    $mobdir = $iliasAbsolutePath . $iliasMobPath . 'mm_' . $mob . '/';
    if (is_dir($mobdir) && ($d = dir($mobdir))) {
        $i = 0;
        while (false !== ($entry = $d->read())) {
            $ext = strtolower(substr(strrchr($entry, '.'), 1));
            if (is_file($mobdir . $entry) && in_array($ext, $tinyMCE_valid_imgs)) {
                $mob_details[$uploadedFile]['file_name'] = $entry;
                $mob_details[$uploadedFile]['file_dir'] = $mobdir;
                $mob_details[$uploadedFile]['http_dir'] = $iliasHttpPath . $iliasMobPath . 'mm_' . $mob . '/';
            }
        }
        $d->close();
    }
}

$response = [];
$uploaded_file_desc = [];
if ($errors->fields || $errors->general) {
    $response[] = $errors;
} elseif ($uploadedFile && $mob_details[$uploadedFile]) {
    $location = $mob_details[$uploadedFile]['http_dir'] . $mob_details[$uploadedFile]['file_name'];
    $uploaded_file_desc['width'] = 0;
    $uploaded_file_desc['height'] = 0;
    $uploaded_file_desc['location'] = $location;
}
$response = [
    'uploaded_file' => $uploaded_file_desc,
    'errors' => $errors,
    'panel' => $panel
];

$DIC->http()->saveResponse(
    $DIC->http()->response()
        ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
        ->withBody(\ILIAS\Filesystem\Stream\Streams::ofString(json_encode(['response' => $response], JSON_THROW_ON_ERROR)))
);
$DIC->http()->sendResponse();
$DIC->http()->close();
