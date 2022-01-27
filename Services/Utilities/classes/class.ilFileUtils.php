<?php
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
/** @defgroup ServicesUtilities Services/Utilities
 */

use ILIAS\Filesystem\Definitions\SuffixDefinitions;

/**
 * Class ilFileUtils
 *
 * @deprecated All Methods are widely used and there is currently no other service
 *             providing all of them, but please do not implement new methods in
 *            this class.
 *
 * @author     Jan Hippchen <janhippchen@gmx.de>
 *
 * @ingroup    ServicesUtilities
 */
class ilFileUtils
{

    /**
     * @var array
     */
    protected static $new_files = array();


    /**
     * @deprecated Will be removed with ILIAS 8
     */

    public static function processZipFile($a_directory, string $a_file, $structure, $ref_id = null, $containerType = null, $tree = null, $access_handler = null): void
    {
        global $DIC;

        $lng = $DIC->language();

        self::$new_files = array();

        $pathinfo = pathinfo($a_file);
        $file = $pathinfo["basename"];

        // see 22727
        if ($pathinfo["extension"] == "") {
            $file .= ".zip";
        }

        // Copy zip-file to new directory, unzip and remove it
        // TODO: check archive for broken file
        //copy ($a_file, $a_directory . "/" . $file);
        ilUtil::moveUploadedFile($a_file, $file, $a_directory . "/" . $file);
        ilUtil::unzip($a_directory . "/" . $file);
        unlink($a_directory . "/" . $file);
        //echo "-".$a_directory . "/" . $file."-";
        // Stores filename and paths into $filearray to check for viruses
        // Checks if filenames can be read, else -> throw exception and leave
        $filearray = [];
        ilFileUtils::recursive_dirscan($a_directory, $filearray);

        // if there are no files unziped (->broken file!)
        if (empty($filearray)) {
            throw new ilFileUtilsException($lng->txt("archive_broken"), ilFileUtilsException::$BROKEN_FILE);
        }

        // virus handling
        foreach ($filearray["file"] as $key => $value) {
            // remove "invisible" files
            if (substr($value, 0, 1) == "." || stristr($filearray["path"][$key], "/__MACOSX/")) {
                unlink($filearray["path"][$key] . $value);
                unset($filearray["path"][$key]);
                unset($filearray["file"][$key]);
                continue;
            }

            $vir = ilUtil::virusHandling($filearray["path"][$key], $value);
            if (!$vir[0]) {
                // Unlink file and throw exception
                unlink($filearray['path'][$key]);
                throw new ilFileUtilsException($lng->txt("file_is_infected") . "<br />" . $vir[1], ilFileUtilsException::$INFECTED_FILE);
            } elseif ($vir[1] != "") {
                throw new ilFileUtilsException($vir[1], ilFileUtilsException::$INFECTED_FILE);
            }
        }

        // If archive is to be used "flat"
        if (!$structure) {
            foreach (array_count_values($filearray["file"]) as $key => $value) {
                // Archive contains same filenames in different directories
                if ($value != "1") {
                    $doublettes .= " '" . ilFileUtils::utf8_encode($key) . "'";
                }
            }
            if (isset($doublettes)) {
                throw new ilFileUtilsException(
                    $lng->txt("exc_upload_error") . "<br />" . $lng->txt("zip_structure_error") . $doublettes,
                    ilFileUtilsException::$DOUBLETTES_FOUND
                );
            }
        } else {
            $mac_dir = $a_directory . "/__MACOSX";
            if (file_exists($mac_dir)) {
                ilUtil::delDir($mac_dir);
            }
        }

        // Everything fine since we got here; so we can store files and folders into the system (if ref_id is given)
        if ($ref_id != null) {
            ilFileUtils::createObjects($a_directory, $structure, $ref_id, $containerType, $tree, $access_handler);
        }
    }


    /**
     * Recursively scans a given directory and writes path and filename into referenced array
     *
     * @param string $dir Directory to start from
     * @param array &$arr Referenced array which is filled with Filename and path
     *
     * @throws ilFileUtilsException
     * @version 1.6.9.07
     * @author  Jan Hippchen
     */
    public static function recursive_dirscan(string $dir, array &$arr): void
    {
        global $DIC;

        $lng = $DIC->language();

        $dirlist = opendir($dir);
        while (false !== ($file = readdir($dirlist))) {
            if (!is_file($dir . "/" . $file) && !is_dir($dir . "/" . $file)) {
                throw new ilFileUtilsException($lng->txt("filenames_not_supported"), ilFileUtilsException::$BROKEN_FILE);
            }

            if ($file != '.' && $file != '..') {
                $newpath = $dir . '/' . $file;
                $level = explode('/', $newpath);
                if (is_dir($newpath)) {
                    ilFileUtils::recursive_dirscan($newpath, $arr);
                } else {
                    $arr["path"][] = $dir . "/";
                    $arr["file"][] = end($level);
                }
            }
        }
        closedir($dirlist);
    }


    /**
     * Creates and inserts file object into tree
     *
     * @param string  $filename Name of the object
     * @param string  $path     Path to file
     * @param integer $ref_id   ref_id of parent
     *
     * @version 1.6.9.07
     * @author  Jan Hippchen
     * @deprecated
     */
    public static function createFile(string $filename, string $path, int $ref_id, $tree = null, $access_handler = null): void
    {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();
        $lng = $DIC->language();
        $ilErr = $DIC["ilErr"];

        if (!$access_handler) {
            $permission = $rbacsystem->checkAccess("create", $ref_id, "file");
        } else {
            $permission = $access_handler->checkAccess("create", "", $ref_id, "file");
        }
        if ($permission) {

            $fileObj = new ilObjFile();
            $fileObj->setType('file');
            $fileObj->setTitle(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
            $fileObj->setFileName(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
            $fileObj->setFileType(ilObjMediaObject::getMimeType($path . "/" . $filename));
            $fileObj->setFileSize(filesize($path . "/" . $filename));
            $fileObj->create();

            // repository
            if (!$access_handler) {
                $fileObj->createReference();
                $fileObj->putInTree($ref_id);
                $fileObj->setPermissions($ref_id);

                self::$new_files[$ref_id][] = $fileObj;
            } else {
                $node_id = $tree->insertObject($ref_id, $fileObj->getId());
                $access_handler->setPermissions($ref_id, $node_id);
            }

            // upload file to filesystem
            $fileObj->createDirectory();
            $fileObj->storeUnzipedFile($path . "/" . $filename, ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
        } else {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
    }


    /**
     * @return mixed[]
     */
    public static function getNewObjects(): array
    {
        return self::$new_files;
    }


    /**
     * utf8-encodes string if it is not a valid utf8-string.
     *
     * @param string $string String to encode
     *
     * @return string utf-8-encoded string
     * @author  Jan Hippchen
     * @version 1.12.3.08
     */
    public static function utf8_encode(string $string): string
    {

        // From http://w3.org/International/questions/qa-forms-utf-8.html
        return (preg_match('%^(?:
			[\x09\x0A\x0D\x20-\x7E]            # ASCII
			| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
			)*$%xs', $string)) ? $string : utf8_encode($string);
    }


    /**
     *    decodes base encoded file row by row to prevent memory exhaust
     *
     * @param string $filename name of file to read
     * @param string $fileout  name where to put decoded file
     */
    public static function fastBase64Decode($filein, string $fileout): bool
    {
        $fh = fopen($filein, 'rb');
        $fh2 = fopen($fileout, 'wb');
        stream_filter_append($fh2, 'convert.base64-decode');

        while (!feof($fh)) {
            $chunk = fgets($fh);
            if ($chunk === false) {
                break;
            }
            fwrite($fh2, $chunk);
        }
        fclose($fh);
        fclose($fh2);

        return true;
    }


    /**
     *    decodes base encoded file row by row to prevent memory exhaust
     *
     * @param string $filename name of file to read
     *
     * @return string base decoded content
     */
    public function fastBase64Encode($filein, $fileout): void
    {
        $fh = fopen($filein, 'rb');
        $fh2 = fopen($fileout, 'wb');
        stream_filter_append($fh2, 'convert.base64-encode');

        while (feof($fh)) {
            $chunk = fgets($fh, 76);
            if ($chunk === false) {
                break;
            }
            fwrite($fh2, $chunk);
        }
        fclose($fh);
        fclose($fh2);
    }


    /**
     * fast uncompressing the file with the zlib-extension without memory consumption
     *
     * @param string $in  filename
     * @param string $out filename
     *
     *
     */
    public function fastGunzip(string $in, string $out): bool
    {
        if (!file_exists($in) || !is_readable($in)) {
            return false;
        }
        if ((!file_exists($out) && !is_writable(dirname($out)) || (file_exists($out) && !is_writable($out)))) {
            return false;
        }

        $in_file = gzopen($in, "rb");
        $out_file = fopen($out, "wb");

        while (!gzeof($in_file)) {
            $buffer = gzread($in_file, 4096);
            fwrite($out_file, $buffer, 4096);
        }

        gzclose($in_file);
        fclose($out_file);

        return true;
    }


    /**
     * @return string $mimeType
     */
    public static function lookupContentMimeType(string $content)
    {
        $finfo = new finfo(FILEINFO_MIME);

        return $finfo->buffer($content);
    }


    /**
     * @return string $mimeType
     */
    public static function lookupFileMimeType(string $a_file)
    {
        if (!file_exists($a_file) || !is_readable($a_file)) {
            return false;
        }

        return self::lookupContentMimeType(file_get_contents($a_file));
    }


    /**
     * @param string file absolute path to file
     *
     * @return string $mimeType
     */
    public static function _lookupMimeType($a_file): string
    {
        return self::lookupFileMimeType($a_file);
    }
    
    /**
     * @deprecated
     */
    public static function getValidFilename($a_filename): string
    {
        global $DIC;
        $sanitizer = new ilFileServicesFilenameSanitizer($DIC->fileServiceSettings());
        
        return $sanitizer->sanitize($a_filename);
    }
    
    /**
     * @deprecated
     */
    public static function rename($a_source, $a_target): bool
    {
        $pi = pathinfo($a_target);
        global $DIC;
        $sanitizer = new ilFileServicesFilenameSanitizer($DIC->fileServiceSettings());
        
        if (!$sanitizer->isClean($a_target)) {
            throw new ilFileUtilsException("Invalid target file");
        }
        
        return rename($a_source, $a_target);
    }
}
