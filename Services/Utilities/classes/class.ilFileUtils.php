<?php
/*
+-----------------------------------------------------------------------------+
| ILIAS open source                                                           |
+-----------------------------------------------------------------------------+
| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

/** @defgroup ServicesUtilities Services/Utilities
 */

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
     * unzips in given directory and processes uploaded zip for use as single files
     *
     * @param string  $a_directory Directory to unzip
     * @param string  $a_file      Filename of archive
     * @param boolean structure  True if archive structure is to be overtaken
     * @param integer $ref_id      ref_id of parent object, if null, files wont be included in system (just checked)
     * @param string containerType object type of created containerobjects (folder or category)
     *
     * @throws ilFileUtilsException
     * @throws ilException
     * @author  Jan Hippchen
     * @version 1.6.9.07
     */

    public static function processZipFile($a_directory, $a_file, $structure, $ref_id = null, $containerType = null, $tree = null, $access_handler = null)
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
                unlink($filearray[path][$key]);
                throw new ilFileUtilsException($lng->txt("file_is_infected") . "<br />" . $vir[1], ilFileUtilsException::$INFECTED_FILE);
                break;
            } else {
                if ($vir[1] != "") {
                    throw new ilFileUtilsException($vir[1], ilFileUtilsException::$INFECTED_FILE);
                    break;
                }
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
                throw new ilFileUtilsException($lng->txt("exc_upload_error") . "<br />" . $lng->txt("zip_structure_error") . $doublettes,
                    ilFileUtilsException::$DOUBLETTES_FOUND);
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
    public static function recursive_dirscan($dir, &$arr)
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
     * Recursively scans a given directory and creates file and folder/category objects
     *
     * Calls createContainer & createFile to store objects in tree
     *
     * @param string  $dir    Directory to start from
     * @param boolean structure  True if archive structure is to be overtaken (otherwise flat inclusion)
     * @param integer $ref_id ref_id of parent object, if null, files won�t be included in system (just checked)
     * @param string containerType object type of created containerobjects (folder or category)
     *
     * @return integer errorcode
     * @throws ilFileUtilsException
     * @author  Jan Hippchen
     * @version 1.6.9.07
     */
    public static function createObjects($dir, $structure, $ref_id, $containerType, $tree = null, $access_handler = null)
    {
        $dirlist = opendir($dir);

        while (false !== ($file = readdir($dirlist))) {
            if (!is_file($dir . "/" . $file) && !is_dir($dir . "/" . $file)) {
                throw new ilFileUtilsException($lng->txt("filenames_not_supported"), ilFileUtilsException::$BROKEN_FILE);
            }
            if ($file != '.' && $file != '..') {
                $newpath = $dir . '/' . $file;
                $level = explode('/', $newpath);
                if (is_dir($newpath)) {
                    if ($structure) {
                        $new_ref_id = ilFileUtils::createContainer(ilFileUtils::utf8_encode($file), $ref_id, $containerType, $tree, $access_handler);
                        ilFileUtils::createObjects($newpath, $structure, $new_ref_id, $containerType, $tree, $access_handler);
                    } else {
                        ilFileUtils::createObjects($newpath, $structure, $ref_id, $containerType, $tree, $access_handler);
                    }
                } else {
                    ilFileUtils::createFile(end($level), $dir, $ref_id, $tree, $access_handler);
                }
            }
        }
        closedir($dirlist);
    }


    /**
     * Creates and inserts container object (folder/category) into tree
     *
     * @param string  $name          Name of the object
     * @param integer $ref_id        ref_id of parent
     * @param string  $containerType Fold or Cat
     *
     * @return integer ref_id of containerobject
     * @author  Jan Hippchen
     * @version 1.6.9.07
     */
    public static function createContainer($name, $ref_id, $containerType, $tree = null, $access_handler = null)
    {
        switch ($containerType) {
            case "Category":
                include_once("./Modules/Category/classes/class.ilObjCategory.php");
                $newObj = new ilObjCategory();
                $newObj->setType("cat");
                break;

            case "Folder":
                include_once("./Modules/Folder/classes/class.ilObjFolder.php");
                $newObj = new ilObjFolder();
                $newObj->setType("fold");
                break;

            case "WorkspaceFolder":
                include_once("./Modules/WorkspaceFolder/classes/class.ilObjWorkspaceFolder.php");
                $newObj = new ilObjWorkspaceFolder();
                break;
        }

        $newObj->setTitle($name);
        $newObj->create();

        // repository
        if (!$access_handler) {
            $newObj->createReference();
            $newObj->putInTree($ref_id);
            $newObj->setPermissions($ref_id);

            if ($newObj->getType() == "cat") {
                global $DIC;

                $lng = $DIC->language();
                $newObj->addTranslation($name, "", $lng->getLangKey(), $lng->getLangKey());
            }

            self::$new_files[$ref_id][] = $newObj;

            return $newObj->getRefId();
        } // workspace
        else {
            $node_id = $tree->insertObject($ref_id, $newObj->getId());
            $access_handler->setPermissions($ref_id, $node_id);

            return $node_id;
        }
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
     */
    public static function createFile($filename, $path, $ref_id, $tree = null, $access_handler = null)
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

            // create and insert file in grp_tree
            include_once("./Modules/File/classes/class.ilObjFile.php");
            $fileObj = new ilObjFile();
            $fileObj->setType('file');
            $fileObj->setTitle(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
            $fileObj->setFileName(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));

            // better use this, mime_content_type is deprecated
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
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
     * @return array
     */
    public static function getNewObjects()
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
    public static function utf8_encode($string)
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
     *
     * @return bool
     */
    public static function fastBase64Decode($filein, $fileout)
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
    function fastBase64Encode($filein, $fileout)
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
     *
     * fast compressing the file with the zlib-extension without memory consumption
     *
     * @param string $in    filename
     * @param string $out   filename
     * @param string $level compression level from 1 to 9
     *
     * @return bool
     */
    private function fastGZip($in, $out, $level = "9")
    {
        if (!file_exists($in) || !is_readable($in)) {
            return false;
        }
        if ((!file_exists($out) && !is_writable(dirname($out)) || (file_exists($out) && !is_writable($out)))) {
            return false;
        }

        $in_file = fopen($in, "rb");
        if (!$out_file = gzopen($out, "wb" . $param)) {
            return false;
        }

        while (!feof($in_file)) {
            $buffer = fgets($in_file, 4096);
            gzwrite($out_file, $buffer, 4096);
        }

        fclose($in_file);
        gzclose($out_file);

        return true;
    }


    /**
     * fast uncompressing the file with the zlib-extension without memory consumption
     *
     * @param string $in  filename
     * @param string $out filename
     *
     * @return bool
     *
     */
    public function fastGunzip($in, $out)
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
     * @param string $content
     *
     * @return string $mimeType
     */
    public static function lookupContentMimeType($content)
    {
        $finfo = new finfo(FILEINFO_MIME);

        return $finfo->buffer($content);
    }


    /**
     * @param string $a_file
     *
     * @return string $mimeType
     */
    public static function lookupFileMimeType($a_file)
    {
        if (!file_exists($a_file) or !is_readable($a_file)) {
            return false;
        }

        return self::lookupContentMimeType(file_get_contents($a_file));
    }


    /**
     * @param string file absolute path to file
     *
     * @return string $mimeType
     */
    public static function _lookupMimeType($a_file)
    {
        return self::lookupFileMimeType($a_file);
    }


    /**
     * Valid extensions
     *
     * @return array valid file extensions
     */
    public static function getValidExtensions()
    {
        global $DIC;

        $setting = $DIC->settings();

        // default white list
        $whitelist = self::getDefaultValidExtensionWhiteList();

        // remove custom black list values
        foreach (explode(",", $setting->get("suffix_repl_additional")) as $custom_black) {
            $custom_black = trim(strtolower($custom_black));
            if (($key = array_search($custom_black, $whitelist)) !== false) {
                unset($whitelist[$key]);
            }
        }

        // add custom white list values
        foreach (explode(",", $setting->get("suffix_custom_white_list")) as $custom_white) {
            $custom_white = trim(strtolower($custom_white));
            if (!in_array($custom_white, $whitelist)) {
                $whitelist[] = $custom_white;
            }
        }

        // bugfix mantis 25498: add an empty entry to ensure that files without extensions are still valid
        $whitelist[] = '';

        return $whitelist;
    }


    /**
     * Valid extensions
     *
     * @return array valid file extensions
     */
    public static function getDefaultValidExtensionWhiteList()
    {
        return array(
            '3gp',    // VIDEO__3_GPP
            '7z',                        // application/x-7z-compressed
            'ai',    // APPLICATION__POSTSCRIPT
            'aif',    // AUDIO__AIFF
            'aifc', // AUDIO__AIFF
            'aiff', // AUDIO__AIFF
            'au',    // AUDIO__BASIC
            'arw',  // IMAGE__X_SONY_ARW
            'avi',  // AUDIO__BASIC
            'backup', // scorm wbts
            'bak', // scorm wbts
            'bas',                        // SPSS script
            'bpmn', // bpmn
            'bpmn2', // bpmn2
            'bmp',    // IMAGE__BMP
            'bib',    // bibtex
            'bibtex',    // bibtex
            'bz',    // APPLICATION__X_BZIP
            'bz2',    // APPLICATION__X_BZIP2
            'c',    // TEXT__PLAIN
            'c++',    // TEXT__PLAIN
            'cc',    // TEXT__PLAIN
            'cct', // scorm wbts
            'cdf',                        // (Wolfram) Computable Document Format
            'cer',    // APPLICATION__X_X509_CA_CERT
            'class', // APPLICATION__X_JAVA_CLASS
            'cls',                        // SPSS script
            'conf',     // TEXT__PLAIN
            'cpp',    // TEXT__X_C
            'crt',    // APPLICATION__X_X509_CA_CERT
            'crs', // scorm wbts
            'crw', // IMAGE__X_CANON_CRW
            'cr2', // IMAGE__X_CANON_CR2
            'css',    // TEXT__CSS
            'cst', // scorm wbts
            'csv',
            'cur', // scorm wbts
            'db', // scorm wbts
            'dcr', // scorm wbts
            'des', // scorm wbts
            'dng', // IMAGE__X_ADOBE_DNG
            'doc',   // APPLICATION__MSWORD,
            'docx',   // APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_DOCUMENT,
            'dot',   // APPLICATION__MSWORD,
            'dotx',   // APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_TEMPLATE,
            'dtd',
            'dvi',   // APPLICATION__X_DVI,
            'el',   // TEXT__X_SCRIPT_ELISP,
            'eps',   // APPLICATION__POSTSCRIPT,
            'epub',   // APPLICATION__EPUB,
            'f',   // TEXT__X_FORTRAN,
            'f77',   // TEXT__X_FORTRAN,
            'f90',   // TEXT__X_FORTRAN,
            'flv',   // VIDEO__X_FLV,
            'for',   // TEXT__X_FORTRAN,
            'g3',   // IMAGE__G3FAX,
            'gif',   // IMAGE__GIF,
            'gl',   // VIDEO__GL,
            'gan',
            'gsd',   // AUDIO__X_GSM,
            'gsm',   // AUDIO__X_GSM,
            'gtar',   // APPLICATION__X_GTAR,
            'gz',   // APPLICATION__X_GZIP,
            'gzip',   // APPLICATION__X_GZIP,
            'h',    // TEXT__X_C
            'hpp',    // TEXT__X_C
            'htm',   // TEXT__HTML,
            'html',   // TEXT__HTML,
            'htmls',   // TEXT__HTML,
            'ibooks', // Apple IBook Format
            'ico',   // IMAGE__X_ICON,
            'ics',   // iCalendar, TEXT__CALENDAR
            'ini', // scorm wbts
            'ipynb',                        // iPython file for Jupyter Notebooks
            'java',   // TEXT__X_JAVA_SOURCE,
            'jbf', // scorm wbts
            'jpeg',   // IMAGE__PJPEG,
            'jpg',   // IMAGE__JPEG,
            'js',   // APPLICATION__X_JAVASCRIPT,
            'jsf', // scorm wbts
            'jso', // scorm wbts
            'json',        // APPLICATION__JSON
            'latex',   // APPLICATION__X_LATEX,
            'lang',   // lang files
            'less', // less
            'log',   // TEXT__PLAIN,
            'lsp',   // APPLICATION__X_LISP,
            'ltx',   // APPLICATION__X_LATEX,
            'm1v',   // VIDEO__MPEG,
            'm2a',   // AUDIO__MPEG,
            'm2v',   // VIDEO__MPEG,
            'm3u',   // AUDIO__X_MPEQURL,
            'm4a',   // AUDIO__MP4,
            'm4v',   // VIDEO__MP4,
            'markdown',    // TEXT__MARKDOWN,
            'm',     // MATLAB
            'mat',   // MATLAB
            'md',    // TEXT__MARKDOWN,
            'mdl',                // Vensim files
            'mdown',    // TEXT__MARKDOWN,
            'mid',   // AUDIO__MIDI,
            'min',        // scorm articulate?
            'midi',   // AUDIO__MIDI,
            'mobi',   // APPLICATION__X_MOBI,
            'mod',   // AUDIO__MOD,
            'mov',   // VIDEO__QUICKTIME,
            'movie',   // VIDEO__X_SGI_MOVIE,
            'mp2',   // AUDIO__X_MPEG,
            'mp3',   // AUDIO__X_MPEG3,
            'mp4',   // VIDEO__MP4,
            'mpa',   // AUDIO__MPEG,
            'mpeg',   // VIDEO__MPEG,
            'mpg',   // AUDIO__MPEG,
            'mph',   // COMSOL Multiphysics
            'mpga',   // AUDIO__MPEG,
            'mpp',   // APPLICATION__VND_MS_PROJECT,
            'mpt',   // APPLICATION__X_PROJECT,
            'mpv',   // APPLICATION__X_PROJECT,
            'mpx',   // APPLICATION__X_PROJECT,
            'mv',   // VIDEO__X_SGI_MOVIE,
            'mw',
            'mv4',   // VIDEO__MP4,
            'nb',                        // Wolfram Notebook files
            'nbp',                        // Wolfram Notebook Player files
            'nef',   // IMAGE__X_NIKON_NEF,
            'nif',   // IMAGE__X_NIFF,
            'niff',   // IMAGE__X_NIFF,
            'obj',                    // Wavefront .obj file
            'obm',                        // SPSS script
            'odt',   // Open document text,
            'ods',   // Open document spreadsheet,
            'odp',   // Open document presentation,
            'odg',   // Open document graphics,
            'odf',   // Open document formula,
            'oga',   // AUDIO__OGG,
            'ogg',   // AUDIO__OGG,
            'ogv',   //  VIDEO__OGG,
            'old',   //  no real file extension, but used in mail/forum components,
            'p',   //  TEXT__X_PASCAL,
            'pas',   //  TEXT__PASCAL,
            'pbm',   //  IMAGE__X_PORTABLE_BITMAP,
            'pcl',   //  APPLICATION__VND_HP_PCL,
            'pct',   //  IMAGE__X_PICT,
            'pcx',   // IMAGE__X_PCX,
            'pdf',   // APPLICATION__PDF,
            'pgm',   // IMAGE__X_PORTABLE_GRAYMAP,
            'pic',   // IMAGE__PICT,
            'pict',   // IMAGE__PICT,
            'png',   // IMAGE__PNG,
            'por',    // Portable SPSS file
            'pov',   // MODEL__X_POV,
            'project', // scorm wbts
            'properties', // scorm wbts
            'ppa',   // APPLICATION__VND_MS_POWERPOINT,
            'ppm',   // IMAGE__X_PORTABLE_PIXMAP,
            'pps',   // APPLICATION__VND_MS_POWERPOINT,
            'ppsx',   // APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_SLIDESHOW,
            'ppt',   // APPLICATION__POWERPOINT,
            'pptx',   // APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_PRESENTATION,
            'ppz',   // APPLICATION__MSPOWERPOINT,
            'ps',   // APPLICATION__POSTSCRIPT,
            'psd', // scorm wbts
            'pwz',   // APPLICATION__VND_MS_POWERPOINT,
            'qt',   // VIDEO__QUICKTIME,
            'qtc',   // VIDEO__X_QTC,
            'qti',   // IMAGE__X_QUICKTIME,
            'qtif',   // IMAGE__X_QUICKTIME,
            'r',    // R script file
            'ra',   // AUDIO__X_PN_REALAUDIO,
            'ram',   // AUDIO__X_PN_REALAUDIO,
            'rar',    // RAR (application/vnd.rar)
            'rast',   // IMAGE__CMU_RASTER,
            'rda',    // R data file
            'rev',    // RAR (application/vnd.rar)
            'rexx',   // TEXT__X_SCRIPT_REXX,
            'ris',    // ris
            'rf',   // IMAGE__VND_RN_REALFLASH,
            'rgb',   // IMAGE__X_RGB,
            'rm',   // APPLICATION__VND_RN_REALMEDIA,
            'rmd',    // R Markdown file
            'rmi',   // AUDIO__MID,
            'rmm',   // AUDIO__X_PN_REALAUDIO,
            'rmp',   // AUDIO__X_PN_REALAUDIO,
            'rt',   // TEXT__RICHTEXT,
            'rtf',   // TEXT__RICHTEXT,
            'rtx',   // TEXT__RICHTEXT,
            'rv',   // VIDEO__VND_RN_REALVIDEO,
            's',   // TEXT__X_ASM,
            's3m',   // AUDIO__S3M,
            'sav',   // SPSS data file
            'sbs',    // SPSS script
            'sec',   //
            'sdml',   // TEXT__PLAIN,
            'sgm',   // TEXT__SGML,
            'sgml',   // TEXT__SGML
            'smi',   // APPLICATION__SMIL,
            'smil',   // APPLICATION__SMIL,
            'sps',    // SPSS syntax file
            'spv',    // SPSS output file
            'stl',                // Stereolithography CAD file
            'svg',   // IMAGE__SVG_XML,
            'swa', // scorm wbts
            'swf',   // APPLICATION__X_SHOCKWAVE_FLASH,
            'swz', // scorm wbts
            'tar',                // application/x-tar
            'tex',   // APPLICATION__X_TEX,
            'texi',   // APPLICATION__X_TEXINFO,
            'texinfo',   // APPLICATION__X_TEXINFO,
            'text',   // TEXT__PLAIN,
            'tgz',   // APPLICATION__X_COMPRESSED,
            'tif',   // IMAGE__TIFF,
            'tiff',   // IMAGE__TIFF,
            'ttf', // scorm wbts
            'txt',   // TEXT__PLAIN,
            'tmp',
            'uvproj',
            'vdf',
            'vimeo',   // VIDEO__VIMEO,
            'viv',   // VIDEO__VIMEO,
            'vivo',   // VIDEO__VIVO,
            'vrml',   // APPLICATION__X_VRML,
            'vsdx',   // viseo
            'wav',        // wav
            'webm',   // VIDEO__WEBM,
            'wmv',   // VIDEO__X_MS_WMV,
            'wmx',   // VIDEO__X_MS_WMX,
            'wmz',   // VIDEO__X_MS_WMZ,
            'woff',   // web open font format,
            'wwd',                        // SPSS script
            'xhtml',   // APPLICATION__XHTML_XML,
            'xif',   // IMAGE__VND_XIFF,
            'xls',   // APPLICATION__EXCEL,
            'xlsx',   // APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET,
            'xmind',
            'xml',   // self::TEXT__XML,
            'xsl',   // APPLICATION__XML,
            'xsd',   // scorm
            'zip'    // APPLICATION__ZIP
        );
    }


    /**
     * Get valid filename
     *
     * @param string filename
     *
     * @return string valid upload filename
     * @throws ilFileUtilsException
     */
    public static function getValidFilename($a_filename)
    {
        if (!self::hasValidExtension($a_filename)) {
            $pi = pathinfo($a_filename);
            // if extension is not in white list, remove all "." and add ".sec" extension
            $basename = str_replace(".", "", $pi["basename"]);
            if (trim($basename) == "") {
                include_once("./Services/Utilities/classes/class.ilFileUtilsException.php");
                throw new ilFileUtilsException("Invalid upload filename.");
            }
            $basename .= ".sec";
            if ($pi["dirname"] != "" && ($pi["dirname"] != "." || substr($a_filename, 0, 2) == "./")) {
                $a_filename = $pi["dirname"] . "/" . $basename;
            } else {
                $a_filename = $basename;
            }
        }

        return $a_filename;
    }


    /**
     * @param string $a_filename
     *
     * @return bool
     */
    public static function hasValidExtension($a_filename)
    {
        $pi = pathinfo($a_filename);

        return (in_array(strtolower($pi["extension"]), self::getValidExtensions()));
    }


    /**
     * Rename a file
     *
     * @param $a_source
     * @param $a_target
     *
     * @return bool
     * @throws ilFileUtilsException
     */
    public static function rename($a_source, $a_target)
    {
        $pi = pathinfo($a_target);
        if (!in_array(strtolower($pi["extension"]), self::getValidExtensions())) {
            include_once("./Services/Utilities/classes/class.ilFileUtilsException.php");
            throw new ilFileUtilsException("Invalid target file " . $pi["basename"] . ".");
        }

        return rename($a_source, $a_target);
    }
}