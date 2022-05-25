<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
use ILIAS\DI\Container;

/**
 * Access class for file objects.
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 * @ingroup ModulesFile
 */
class ilObjFileAccess extends ilObjectAccess implements ilWACCheckingClass
{
    /**
     * Contains an array of extensions separated by space.
     * Since this array is needed for every file object displayed on a
     * repository page, we only create it once, and cache it here.
     */
    protected static array $inline_file_extensions = [];
    
    protected static array $preload_list_gui_data = [];
    
    
    protected function checkAccessToObjectId(int $obj_id) : bool
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
    
    public function canBeDelivered(ilWACPath $ilWACPath) : bool
    {
        switch ($ilWACPath->getSecurePathId()) {
            case 'previews':
                preg_match('/\/previews\/[\d\/]{0,}\/preview_([\d]{0,})\//uU', $ilWACPath->getPath(), $matches);
                $obj_id = (int) $matches[1];
                break;
            default:
                $obj_id = -1;
                break;
        }
        
        return $this->checkAccessToObjectId($obj_id);
    }
    

    
    /**
     * get commands
     * this method returns an array of all possible commands/permission combinations
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     * @return array<int, mixed[]>
     */
    public static function _getCommands() : array
    {
        $commands = array();
        $commands[] = array(
            "permission" => "read",
            "cmd" => "sendfile",
            "lang_var" => "download",
            "default" => true,
        );
        $commands[] = array(
            "permission" => "write",
            "cmd" => ilFileVersionsGUI::CMD_UNZIP_CURRENT_REVISION,
            "lang_var" => "unzip",
        );
        $commands[] = array(
            "permission" => "write",
            "cmd" => "versions",
            "lang_var" => "versions",
        );
        $commands[] = array(
            "permission" => "write",
            "cmd" => "edit",
            "lang_var" => "settings",
        );
        
        return $commands;
    }
    
    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto(string $a_target) : bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        
        $t_arr = explode("_", $a_target);
        
        // personal workspace context: do not force normal login
        if (isset($t_arr[2]) && $t_arr[2] == "wsp") {
            return ilSharedResourceGUI::hasAccess($t_arr[1]);
        }
        
        if ($t_arr[0] != "file" || ((int) $t_arr[1]) <= 0) {
            return false;
        }
        return $ilAccess->checkAccess("visible", "", $t_arr[1])
            || $ilAccess->checkAccess("read", "", $t_arr[1]);
    }

    /**
     * @param int $a_id
     * @deprecated
     */
    public static function _lookupFileSize(int $a_id, bool $by_reference = true) : int
    {
        try {
            $obj = new ilObjFile($a_id, $by_reference);
            return $obj->getFileSize();
        } catch (Throwable $t) {
            return 0;
        }
    }
    
    /**
     * Returns true, if the specified file shall be displayed inline in the browser.
     */
    public static function _isFileInline(string $a_file_name) : bool
    {
        if (self::$inline_file_extensions === []) {
            $settings = new ilSetting('file_access');
            self::$inline_file_extensions = explode(" ", $settings->get('inline_file_extensions'));
        }
        $extension = self::_getFileExtension($a_file_name);
        
        return in_array($extension, self::$inline_file_extensions);
    }
    
    /**
     * Gets the file extension of the specified file name.
     * The file name extension is converted to lower case before it is returned.
     * For example, for the file name "HELLO.MP3", this function returns "mp3".
     * A file name extension can have multiple parts. For the file name
     * "hello.tar.gz", this function returns "gz".
     */
    public static function _getFileExtension(string $a_file_name) : string
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
     * - Filenames starting with '.' are hidden Unix files
     * - Filenames ending with '~' are temporary Unix files
     * - Filenames starting with '~$' are temporary Windows files
     * - The file "Thumbs.db" is a hidden Windows file
     */
    public static function _isFileHidden(string $a_file_name) : bool
    {
        return substr($a_file_name, 0, 1) == '.' || substr($a_file_name, -1, 1) == '~'
            || substr($a_file_name, 0, 2) == '~$'
            || $a_file_name == 'Thumbs.db';
    }
    
    /**
     * Appends the text " - Copy" to a filename in the language of
     * the current user.
     * If the provided $nth_copy parameter is greater than 1, then
     * is appended in round brackets. If $nth_copy parameter is null, then
     * the function determines the copy number on its own.
     * If this function detects, that the filename already ends with " - Copy",
     * or with "- Copy ($nth_copy), it only appends the number of the copy to
     * the filename.
     * This function retains the extension of the filename.
     * Examples:
     * - Calling ilObjFileAccess::_appendCopyToTitle('Hello.txt', 1)
     *   returns: "Hello - Copy.txt".
     * - Calling ilObjFileAccess::_appendCopyToTitle('Hello.txt', 2)
     *   returns: "Hello - Copy (2).txt".
     * - Calling ilObjFileAccess::_appendCopyToTitle('Hello - Copy (3).txt', 2)
     *   returns: "Hello - Copy (2).txt".
     * - Calling ilObjFileAccess::_appendCopyToTitle('Hello - Copy (3).txt', null)
     *   returns: "Hello - Copy (4).txt".
     */
    public static function _appendNumberOfCopyToFilename($a_file_name, $nth_copy = null, $a_handle_extension = false) : string
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
     */
    public static function _getPermanentDownloadLink(int $ref_id) : string
    {
        return ilLink::_getStaticLink($ref_id, "file", true, "_download");
    }
    
    /**
     * @param int[] $obj_ids
     * @param int[] $ref_ids
     */
    public static function _preloadData(array $obj_ids, array $ref_ids) : void
    {
        global $DIC;
        
        $DIC->language()->loadLanguageModule('file');
  
        self::$preload_list_gui_data = [];
        
        $set = $DIC->database()->query("SELECT obj_id,max(hdate) latest" . " FROM history"
            . " WHERE obj_type = " . $DIC->database()->quote("file", "text") . " AND "
            . $DIC->database()->in("obj_id", $obj_ids, "", "integer") . " GROUP BY obj_id");
        while ($row = $DIC->database()->fetchAssoc($set)) {
            self::$preload_list_gui_data[(int) $row["obj_id"]]["date"] = $row["latest"];
        }
        
        $set = $DIC->database()->query("SELECT file_size, version, file_id, page_count, rid" . " FROM file_data" . " WHERE "
            . $DIC->database()->in("file_id", $obj_ids, "", "integer"));
        while ($row = $DIC->database()->fetchAssoc($set)) {
            self::$preload_list_gui_data[(int) $row["file_id"]]["size"] = $row["file_size"] ?? 0;
            self::$preload_list_gui_data[(int) $row["file_id"]]["version"] = $row["version"];
            self::$preload_list_gui_data[(int) $row["file_id"]]["page_count"] = $row["page_count"];
            self::$preload_list_gui_data[(int) $row["file_id"]]["rid"] = $row["rid"];
        }
        
        $res = $DIC->database()->query("SELECT rid, file_id  FROM file_data WHERE rid IS NOT NULL AND " . $DIC->database()->in(
            'file_id',
            $obj_ids,
            false,
            'integer'
        ));
        $rids = [];
        
        while ($row = $DIC->database()->fetchObject($res)) {
            $rids[(int) $row->file_id] = $row->rid;
        }
        $DIC->resourceStorage()->preload($rids);
        
        foreach ($rids as $file_id => $rid) {
            if ($id = $DIC->resourceStorage()->manage()->find($rid)) {
                $max = $DIC->resourceStorage()->manage()->getResource($id)->getCurrentRevision();
                self::$preload_list_gui_data[(int) $file_id]["title"] = $max->getTitle();
                self::$preload_list_gui_data[(int) $file_id]["mime"] = $max->getInformation()->getMimeType();
                self::$preload_list_gui_data[(int) $file_id]["version"] = $max->getVersionNumber();
                self::$preload_list_gui_data[(int) $file_id]["size"] = $max->getInformation()->getSize() ?? 0;
                self::$preload_list_gui_data[(int) $file_id]["date"] = $max->getInformation()->getCreationDate()->format(DATE_ATOM);
            }
        }
    }
    
    public static function getListGUIData(int $a_obj_id) : array
    {
        if (isset(self::$preload_list_gui_data[$a_obj_id])) {
            return self::$preload_list_gui_data[$a_obj_id];
        }
        return [];
    }
}
