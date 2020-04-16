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

chdir('../../../../../');

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/**
 * @var $ilIliasIniFile ilIniFile
 */
global $DIC;

$ilIliasIniFile = $DIC['ilIliasIniFile'];

$htdocs = $ilIliasIniFile->readVariable('server', 'absolute_path') . '/';
$weburl = $ilIliasIniFile->readVariable('server', 'http_path') . '/';
$installpath = $htdocs;


// directory where tinymce files are located
$iliasMobPath = 'data/' . CLIENT_ID . '/mobs/';
$iliasAbsolutePath = $htdocs;
$iliasHttpPath = $weburl;
// base url for images
$tinyMCE_base_url = $weburl;
$tinyMCE_DOC_url = $installpath;

if ($iliasHttpPath) {
    /**
     * @var $https ilHttps
     */
    global $DIC;

    $https = $DIC['https'];

    if (strpos($iliasHttpPath, 'https://') === false && $https->isDetected()) {
        $iliasHttpPath = str_replace('http://', 'https://', $iliasHttpPath);
    }
}

// allowed extentions for uploaded image files
$tinyMCE_valid_imgs = array('gif', 'jpg', 'jpeg', 'png');

// allow upload in image library
$tinyMCE_upload_allowed = true;

include_once 'webservice/soap/include/inc.soap_functions.php';
$mobs = ilSoapFunctions::getMobsOfObject(session_id() . '::' . CLIENT_ID, $_GET['obj_type'] . ':html', (int) $_GET['obj_id']);
$preview = '';


$img = isset($_POST['imglist']) ? $_POST['imglist'] : '';
$_root = $installpath;
$errors = array();

// upload images
if (isset($_FILES['img_file']['size']) && $_FILES['img_file']['size'] > 0) {
    include_once 'webservice/soap/include/inc.soap_functions.php';
    $safefilename = preg_replace('/[^a-zA-z0-9_\.]/', '', $_FILES['img_file']['name']);
    $media_object = ilSoapFunctions::saveTempFileAsMediaObject(session_id() . '::' . CLIENT_ID, $safefilename, $_FILES['img_file']['tmp_name']);
    if (file_exists($iliasAbsolutePath . $iliasMobPath . 'mm_' . $media_object->getId() . '/' . $media_object->getTitle())) {
        // only save usage if the file was uploaded
        $media_object->_saveUsage($media_object->getId(), $_GET['obj_type'] . ':html', (int) $_GET['obj_id']);
    }
    $preview = $iliasHttpPath . $iliasMobPath . 'mm_' . $media_object->getId() . '/' . $media_object->getTitle();
    $mobs[$media_object->getId()] = $media_object->getId();
}

$tpl = new ilTemplate(dirname(__FILE__) . '/tpl.imagemanager.html', true, true);

// delete image
if ($tinyMCE_img_delete_allowed && isset($_POST['lib_action'])
    && ($_POST['lib_action'] == 'delete') && !empty($img)) {
    deleteImg();
}

if ($tinyMCE_img_delete_allowed) {
    $tpl->touchBlock("delete_allowed");
}
outMobImages();
outMobImageParams();
$tpl->setVariable('OBJ_ID', (int) $_GET['obj_id']);
$tpl->setVariable('OBJ_TYPE', $_GET['obj_type']);
$tpl->setVariable('VALUE_UPDATE', (int) $_GET['update']);
$tpl->setVariable('ILIAS_INST_PATH', $iliasHttpPath);
if ($_GET['update'] == 1) {
    $tpl->setVariable('INSERT_COMMAND', '{#update}');
} else {
    $tpl->setVariable('INSERT_COMMAND', '{#insert}');
}
$tpl->setVariable('URL_PREVIEW', $preview);
$error_messages = '';
if (!empty($errors)) {
    $error_messages .= '<span class="error">';
    foreach ($errors as $err) {
        $error_messages .= $err . '<br />';
    }
    $error_messages .= '</span>';
}
$tpl->setVariable('ERROR_MESSAGES', $error_messages);
$tpl->show();

function outMobImages()
{
    /**
     * @var $tpl ilTemplate
     */
    global $DIC, $mobs, $iliasMobPath, $iliasAbsolutePath, $iliasHttpPath, $tinyMCE_valid_imgs, $errors, $img, $arr_tinyMCE_image_files;

    $tpl = $DIC['tpl'];

    $arr_tinyMCE_image_files = array();

    $i = 0;
    // read image directory
    foreach ($mobs as $mob) {
        $mobdir = $iliasAbsolutePath . $iliasMobPath . 'mm_' . $mob . '/';
        $d = @dir($mobdir);
        if ($d) {
            while (false !== ($entry = $d->read())) {
                $ext = strtolower(substr(strrchr($entry, '.'), 1));
                if (is_file($mobdir . $entry) && in_array($ext, $tinyMCE_valid_imgs)) {
                    $arr_tinyMCE_image_files[$i]['file_name'] = $entry;
                    $arr_tinyMCE_image_files[$i]['file_dir'] = $mobdir;
                    $arr_tinyMCE_image_files[$i]['http_dir'] = $iliasHttpPath . $iliasMobPath . 'mm_' . $mob . '/';
                    $i++;
                }
            }
            $d->close();
        } else {
            $errors[] = '{#ibrowser.errornodir}';
        }
    }
    // sort the list of image filenames alphabetically.
    sort($arr_tinyMCE_image_files);

    for ($k = 0; $k < count($arr_tinyMCE_image_files); $k++) {
        $entry = $arr_tinyMCE_image_files[$k]['file_name'];
        $size = getimagesize($arr_tinyMCE_image_files[$k]['file_dir'] . $entry);
        $fsize = filesize($arr_tinyMCE_image_files[$k]['file_dir'] . $entry);
        $tpl->setCurrentBlock('imagefile');
        $tpl->setVariable('IMAGEFILE_VALUE', $arr_tinyMCE_image_files[$k]['http_dir']);
        $tpl->setVariable('IMAGEFILE_TEXT', $entry);
        if ($entry == $img) {
            $tpl->setVariable('IMAGEFILE_SELECTED', ' selected=\'selected\'');
        }
        $tpl->parseCurrentBlock();
    }
}

function deleteImg()
{
}

function outMobImageParams()
{
    global $DIC, $arr_tinyMCE_image_files;

    $tpl = $DIC['tpl'];
    for ($k = 0; $k < count($arr_tinyMCE_image_files); $k++) {
        $tpl->setCurrentBlock('imageparams');
        $entry = $arr_tinyMCE_image_files[$k]['file_name'];
        $size = getimagesize($arr_tinyMCE_image_files[$k]['file_dir'] . $entry);
        $fsize = filesize($arr_tinyMCE_image_files[$k]['file_dir'] . $entry);
        $tpl->setVariable('IMG_WIDTH', $size[0]);
        $tpl->setVariable('IMG_HEIGHT', $size[1]);
        $tpl->setVariable('IMG_PATH', $arr_tinyMCE_image_files[$k]['http_dir']);
        $tpl->setVariable('F_SIZE', ilUtil::formatSize($fsize));
        $tpl->parseCurrentBlock();
    }
}

function liboptions($arr, $prefix = '', $sel = '')
{
    $buf = '';
    foreach ($arr as $lib) {
        $buf .= '<option value="' . $lib['value'] . '"' . (($lib['value'] == $sel) ? ' selected' : '') . '>' . $prefix . $lib['text'] . '</option>' . "\n";
    }
    return $buf;
}
