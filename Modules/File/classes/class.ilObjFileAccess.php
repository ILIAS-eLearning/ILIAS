<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
 * Access class for file objects.
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ModulesFile
 */
class ilObjFileAccess extends ilObjectAccess implements ilWACCheckingClass
{

    /**
     * @param $obj_id
     *
     * @return bool
     */
    protected function checkAccessToObjectId($obj_id)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        /**
         * @var $ilAccess ilAccessHandler
         */
        foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($ilAccess->checkAccess('read', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param \ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        switch ($ilWACPath->getSecurePathId()) {
            case 'previews':
                $re = '/\/previews\/[\d\/]{0,}\/preview_([\d]{0,})\//uU';
                break;
        }
        preg_match($re, $ilWACPath->getPath(), $matches);

        return $this->checkAccessToObjectId($matches[1]);
    }



    // BEGIN WebDAV cache inline file extensions


    /**
     * Contains an array of extensions separated by space.
     * Since this array is needed for every file object displayed on a
     * repository page, we only create it once, and cache it here.
     *
     * @see function _isFileInline
     */
    protected static $_inlineFileExtensionsArray;
    // END WebDAV cache inline file extensions

    protected static $preload_list_gui_data; // [array]


    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     */
    static function _getCommands()
    {
        $commands = array();
        $commands[] = array(
            "permission" => "read",
            "cmd"        => "sendfile",
            "lang_var"   => "download",
            "default"    => true,
        );
        $commands[] = array(
            "permission" => "write",
            "cmd"        => "edit",
            "lang_var"   => "edit_content",
        );
        $commands[] = array(
            "permission" => "write",
            "cmd"        => "versions",
            "lang_var"   => "versions",
        );

        return $commands;
    }


    /**
     * check whether goto script will succeed
     */
    static function _checkGoto($a_target)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $t_arr = explode("_", $a_target);

        // personal workspace context: do not force normal login
        if (isset($t_arr[2]) && $t_arr[2] == "wsp") {
            include_once "Services/PersonalWorkspace/classes/class.ilSharedResourceGUI.php";

            return ilSharedResourceGUI::hasAccess($t_arr[1]);
        }

        if ($t_arr[0] != "file" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("visible", "", $t_arr[1])
            || $ilAccess->checkAccess("read", "", $t_arr[1])
        ) {
            return true;
        }

        return false;
    }


    /**
     * looks up the file_data for the file object with the specified object id
     * as an associative array.
     */
    static function _lookupFileData($a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = "SELECT * FROM file_data WHERE file_id = " . $ilDB->quote($a_id, 'integer');
        $r = $ilDB->query($q);
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return $row;
    }


    /**
     * lookup version
     */
    static function _lookupVersion($a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = "SELECT version FROM file_data WHERE file_id = " . $ilDB->quote($a_id, 'integer');
        $r = $ilDB->query($q);
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        $striped = ilUtil::stripSlashes($row->version);

        return $striped > 0 ? $striped : 1;
    }


    /**
     * Quickly looks up the file size from the database and returns the
     * number of bytes.
     */
    public static function _lookupFileSize($a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = "SELECT file_size FROM file_data WHERE file_id = " . $ilDB->quote($a_id, 'integer');
        $r = $ilDB->query($q);
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        $size = $row->file_size;

        return $size;
    }


    /**
     * Looks up the file size by retrieving it from the filesystem.
     * This function runs much slower than _lookupFileSize()! Use this
     * function only, to update the data in the database. For example, if
     * the file size in the database has become inconsistent for some reason.
     */
    public static function _lookupFileSizeFromFilesystem($a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = "SELECT * FROM file_data WHERE file_id = " . $ilDB->quote($a_id, 'integer');
        $r = $ilDB->query($q);
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        require_once('Modules/File/classes/class.ilFSStorageFile.php');
        $fss = new ilFSStorageFile($a_id);
        $file = $fss->getAbsolutePath() . '/' . $row->file_name;

        if (@!is_file($file)) {
            $version_subdir = "/" . sprintf("%03d", ilObjFileAccess::_lookupVersion($a_id));
            $file = $fss->getAbsolutePath() . '/' . $version_subdir . '/' . $row->file_name;
        }

        if (is_file($file)) {
            $size = filesize($file);
        } else {
            $size = 0;
        }

        return $size;
    }


    /**
     * lookup suffix
     */
    static function _lookupSuffix($a_id)
    {
        include_once('Modules/File/classes/class.ilFSStorageFile.php');

        global $DIC;
        $ilDB = $DIC['ilDB'];

        // BEGIN WebDAV: Filename suffix is determined by file title
        $q = "SELECT * FROM object_data WHERE obj_id = " . $ilDB->quote($a_id, 'integer');
        $r = $ilDB->query($q);
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        require_once 'Modules/File/classes/class.ilObjFile.php';

        return self::_getFileExtension($row->title);
        // END WebDAV: Filename suffix is determined by file title
    }


    /**
     * Returns the number of bytes used on the harddisk by the file object
     * with the specified object id.
     *
     * @param int object id of a file object.
     */
    static function _lookupDiskUsage($a_id)
    {
        include_once('Modules/File/classes/class.ilFSStorageFile.php');
        $fileStorage = new ilFSStorageFile($a_id);
        $dir = $fileStorage->getAbsolutePath();

        return ilUtil::dirsize($dir);
    }

    // BEGIN WebDAV: Get file extension, determine if file is inline, guess file type.


    /**
     * Returns true, if the specified file shall be displayed inline in the browser.
     */
    public static function _isFileInline($a_file_name)
    {
        if (self::$_inlineFileExtensionsArray
            === null
        )        // the === makes a huge difference, if the array is empty
        {
            require_once 'Services/Administration/classes/class.ilSetting.php';
            $settings = new ilSetting('file_access');
            self::$_inlineFileExtensionsArray = preg_split('/ /', $settings->get('inline_file_extensions'), -1, PREG_SPLIT_NO_EMPTY);
        }
        $extension = self::_getFileExtension($a_file_name);

        return in_array($extension, self::$_inlineFileExtensionsArray);
    }


    /**
     * Gets the file extension of the specified file name.
     * The file name extension is converted to lower case before it is returned.
     *
     * For example, for the file name "HELLO.MP3", this function returns "mp3".
     *
     * A file name extension can have multiple parts. For the file name
     * "hello.tar.gz", this function returns "gz".
     *
     *
     * @param string $a_file_name The file name
     */
    public static function _getFileExtension($a_file_name)
    {
        if (preg_match('/\.([a-z0-9]+)\z/i', $a_file_name, $matches) == 1) {
            return strtolower($matches[1]);
        } else {
            return '';
        }
    }


    /**
     * Returns true, if a file with the specified name, is usually hidden from
     * the user.
     *
     * - Filenames starting with '.' are hidden Unix files
     * - Filenames ending with '~' are temporary Unix files
     * - Filenames starting with '~$' are temporary Windows files
     * - The file "Thumbs.db" is a hidden Windows file
     */
    public static function _isFileHidden($a_file_name)
    {
        return substr($a_file_name, 0, 1) == '.' || substr($a_file_name, -1, 1) == '~'
            || substr($a_file_name, 0, 2) == '~$'
            || $a_file_name == 'Thumbs.db';
    }
    // END WebDAV: Get file extension, determine if file is inline, guess file type.


    /**
     * Appends the text " - Copy" to a filename in the language of
     * the current user.
     *
     * If the provided $nth_copy parameter is greater than 1, then
     * is appended in round brackets. If $nth_copy parameter is null, then
     * the function determines the copy number on its own.
     *
     * If this function detects, that the filename already ends with " - Copy",
     * or with "- Copy ($nth_copy), it only appends the number of the copy to
     * the filename.
     *
     * This function retains the extension of the filename.
     *
     * Examples:
     * - Calling ilObjFileAccess::_appendCopyToTitle('Hello.txt', 1)
     *   returns: "Hello - Copy.txt".
     *
     * - Calling ilObjFileAccess::_appendCopyToTitle('Hello.txt', 2)
     *   returns: "Hello - Copy (2).txt".
     *
     * - Calling ilObjFileAccess::_appendCopyToTitle('Hello - Copy (3).txt', 2)
     *   returns: "Hello - Copy (2).txt".
     *
     * - Calling ilObjFileAccess::_appendCopyToTitle('Hello - Copy (3).txt', null)
     *   returns: "Hello - Copy (4).txt".
     */
    public static function _appendNumberOfCopyToFilename($a_file_name, $nth_copy = null, $a_handle_extension = false)
    {
        global $DIC;
        $lng = $DIC['lng'];

        $filenameWithoutExtension = $a_file_name;

        $extension = null;
        if ($a_handle_extension) {
            // Get the extension and the filename without the extension
            $extension = ilObjFileAccess::_getFileExtension($a_file_name);
            if (strlen($extension) > 0) {
                $extension = '.' . $extension;
                $filenameWithoutExtension = substr($a_file_name, 0, -strlen($extension));
            }
        }

        // create a regular expression from the language text copy_n_of_suffix, so that
        // we can match it against $filenameWithoutExtension, and retrieve the number of the copy.
        // for example, if copy_n_of_suffix is 'Copy (%1s)', this creates the regular
        // expression '/ Copy \\([0-9]+)\\)$/'.
        $nthCopyRegex = preg_replace('/([\^$.\[\]|()?*+{}])/', '\\\\${1}', ' '
            . $lng->txt('copy_n_of_suffix'));
        $nthCopyRegex = '/' . preg_replace('/%1\\\\\$s/', '([0-9]+)', $nthCopyRegex) . '$/';

        // Get the filename without any previously added number of copy.
        // Determine the number of copy, if it has not been specified.
        if (preg_match($nthCopyRegex, $filenameWithoutExtension, $matches)) {
            // this is going to be at least the third copy of the filename
            $filenameWithoutCopy = substr($filenameWithoutExtension, 0, -strlen($matches[0]));
            if ($nth_copy == null) {
                $nth_copy = $matches[1] + 1;
            }
        } else {
            if (substr($filenameWithoutExtension, -strlen(' ' . $lng->txt('copy_of_suffix')))
                == ' ' . $lng->txt('copy_of_suffix')
            ) {
                // this is going to be the second copy of the filename
                $filenameWithoutCopy = substr($filenameWithoutExtension, 0, -strlen(' '
                    . $lng->txt('copy_of_suffix')));
                if ($nth_copy == null) {
                    $nth_copy = 2;
                }
            } else {
                // this is going to be the first copy of the filename
                $filenameWithoutCopy = $filenameWithoutExtension;
                if ($nth_copy == null) {
                    $nth_copy = 1;
                }
            }
        }

        // Construct the new filename
        if ($nth_copy > 1) {
            // this is at least the second copy of the filename, append " - Copy ($nth_copy)"
            $newFilename = $filenameWithoutCopy . sprintf(' '
                    . $lng->txt('copy_n_of_suffix'), $nth_copy)
                . $extension;
        } else {
            // this is the first copy of the filename, append " - Copy"
            $newFilename = $filenameWithoutCopy . ' ' . $lng->txt('copy_of_suffix') . $extension;
        }

        return $newFilename;
    }


    /**
     * Gets the permanent download link for the file.
     *
     * @param int $ref_id
     *
     * @return string
     */
    public static function _getPermanentDownloadLink($ref_id)
    {
        return ilLink::_getStaticLink($ref_id, "file", true, "_download");
    }


    /**
     * @param array $a_obj_ids
     * @param int[] $a_ref_ids
     */
    public static function _preloadData($a_obj_ids, $a_ref_ids)
    {
        global $DIC;

        self::$preload_list_gui_data = array();

        $set = $DIC->database()->query("SELECT obj_id,max(hdate) latest" . " FROM history"
            . " WHERE obj_type = " . $DIC->database()->quote("file", "text") . " AND "
            . $DIC->database()->in("obj_id", $a_obj_ids, "", "integer") . " GROUP BY obj_id");
        while ($row = $DIC->database()->fetchAssoc($set)) {
            self::$preload_list_gui_data[$row["obj_id"]]["date"] = $row["latest"];
        }

        $set = $DIC->database()->query("SELECT file_size, version, file_id, page_count" . " FROM file_data" . " WHERE "
            . $DIC->database()->in("file_id", $a_obj_ids, "", "integer"));
        while ($row = $DIC->database()->fetchAssoc($set)) {
            self::$preload_list_gui_data[$row["file_id"]]["size"] = $row["file_size"];
            self::$preload_list_gui_data[$row["file_id"]]["version"] = $row["version"];
            self::$preload_list_gui_data[$row["file_id"]]["page_count"] = $row["page_count"];
        }
    }


    /**
     * @param $a_obj_id
     *
     * @return array
     */
    public static function getListGUIData($a_obj_id)
    {
        if (isset(self::$preload_list_gui_data[$a_obj_id])) {
            return self::$preload_list_gui_data[$a_obj_id];
        }
    }
}
