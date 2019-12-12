<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjUserFolder
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObject
*/

require_once "./Services/Object/classes/class.ilObject.php";

define('USER_FOLDER_ID', 7);

class ilObjUserFolder extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id, $a_call_by_reference = true)
    {
        $this->type = "usrf";
        parent::__construct($a_id, $a_call_by_reference);
    }


    /**
    * delete userfolder and all related data
    * DISABLED
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // DISABLED
        return false;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        // put here userfolder specific stuff

        // always call parent delete function at the end!!
        return true;
    }


    public function getExportFilename($a_mode = "userfolder_export_excel_x86")
    {
        $filename = "";
        //$settings = $this->ilias->getAllSettings();
        //$this->inst_id = $settings["inst_id"];
        $inst_id = IL_INST_ID;

        $date = time();

        switch ($a_mode) {
            case "userfolder_export_excel_x86":
                $filename = $date . "__" . $inst_id . "__xls_usrf";
                break;
            case "userfolder_export_csv":
                $filename = $date . "__" . $inst_id . "__csv_usrf.csv";
                break;
            case "userfolder_export_xml":
                $filename = $date . "__" . $inst_id . "__xml_usrf.xml";
                break;
        }
        return $filename;
    }


    /**
    * Get the location of the export directory for the user accounts
    *
    * Get the location of the export directory for the user accounts
    *
    * @access	public
    */
    public function getExportDirectory()
    {
        $export_dir = ilUtil::getDataDir() . "/usrf_data/export";

        return $export_dir;
    }

    /**
    * Get a list of the already exported files in the export directory
    *
    * Get a list of the already exported files in the export directory
    *
    * @return array A list of file names
    * @access	public
    */
    public function getExportFiles()
    {
        $dir = $this->getExportDirectory();

        // quit if export dir not available
        if (!@is_dir($dir) or
            !is_writeable($dir)) {
            return array();
        }

        // open directory
        $dir = dir($dir);

        // initialize array
        $file = array();

        // get files and save the in the array
        while ($entry = $dir->read()) {
            if ($entry != "." and
                $entry != ".." and
                preg_match("/^[0-9]{10}_{2}[0-9]+_{2}([a-z0-9]{3})_usrf\.[a-z]{1,4}\$/", $entry, $matches)) {
                $filearray["filename"] = $entry;
                $filearray["filesize"] = filesize($this->getExportDirectory() . "/" . $entry);
                array_push($file, $filearray);
            }
        }

        // close import directory
        $dir->close();

        // sort files
        sort($file);
        reset($file);

        return $file;
    }

    public function escapeXML($value)
    {
        $value = str_replace("&", "&amp;", $value);
        $value = str_replace("<", "&lt;", $value);
        $value = str_replace(">", "&gt;", $value);
        return $value;
    }

    public function createXMLExport(&$settings, &$data, $filename)
    {
        include_once './Services/User/classes/class.ilUserDefinedData.php';
        include_once './Services/User/classes/class.ilObjUser.php';

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        global $DIC;

        $ilDB = $DIC['ilDB'];
        global $DIC;

        $log = $DIC['log'];

        $file = fopen($filename, "w");

        if (is_array($data)) {
            include_once './Services/User/classes/class.ilUserXMLWriter.php';

            $xmlWriter = new ilUserXMLWriter();
            $xmlWriter->setObjects($data);
            $xmlWriter->setSettings($settings);
            $xmlWriter->setAttachRoles(true);

            if ($xmlWriter->start()) {
                fwrite($file, $xmlWriter->getXML());
            }
        }
    }


    /**
     * Get all exportable user defined fields
     */
    public function getUserDefinedExportFields()
    {
        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        $udf_obj =&ilUserDefinedFields::_getInstance();

        $udf_ex_fields = array();
        foreach ($udf_obj->getDefinitions() as $definition) {
            if ($definition["export"] != false) {
                $udf_ex_fields[] = array("name" => $definition["field_name"],
                    "id" => $definition["field_id"]);
            }
        }

        return $udf_ex_fields;
    }

    public function createCSVExport(&$settings, &$data, $filename)
    {

        // header
        $headerrow = array();
        $udf_ex_fields = $this->getUserDefinedExportFields();
        foreach ($settings as $value) {	// standard fields
            array_push($headerrow, $this->lng->txt($value));
        }
        foreach ($udf_ex_fields as $f) {	// custom fields
            array_push($headerrow, $f["name"]);
        }

        $separator = ";";
        $file = fopen($filename, "w");
        $formattedrow =&ilUtil::processCSVRow($headerrow, true, $separator);
        fwrite($file, join($separator, $formattedrow) . "\n");
        foreach ($data as $row) {
            $csvrow = array();
            foreach ($settings as $header) {	// standard fields
                // multi-text
                if (is_array($row[$header])) {
                    $row[$header] = implode(", ", $row[$header]);
                }
                
                array_push($csvrow, $row[$header]);
            }

            // custom fields
            reset($udf_ex_fields);
            if (count($udf_ex_fields) > 0) {
                include_once("./Services/User/classes/class.ilUserDefinedData.php");
                $udf = new ilUserDefinedData($row["usr_id"]);
                foreach ($udf_ex_fields as $f) {	// custom fields
                    array_push($csvrow, $udf->get("f_" . $f["id"]));
                }
            }

            $formattedrow =&ilUtil::processCSVRow($csvrow, true, $separator);
            fwrite($file, join($separator, $formattedrow) . "\n");
        }
        fclose($file);
    }

    public function createExcelExport(&$settings, &$data, $filename)
    {
        include_once "./Services/Excel/classes/class.ilExcel.php";
        $worksheet = new ilExcel();
        $worksheet->addSheet($this->lng->txt("users"));
        
        $row = 1;
        $col = 0;

        $udf_ex_fields = $this->getUserDefinedExportFields();

        // title row
        foreach ($settings as $value) {	// standard fields
            if ($value == 'ext_account') {
                $value = 'user_ext_account';
            }
            $worksheet->setCell($row, $col, $this->lng->txt($value));
            $col++;
        }
        foreach ($udf_ex_fields as $f) {	// custom fields
            $worksheet->setCell($row, $col, $f["name"]);
            $col++;
        }
        $worksheet->setBold("A1:" . $worksheet->getColumnCoord($col-1) . "1");

        $this->lng->loadLanguageModule("meta");
        foreach ($data as $index => $rowdata) {
            $row++;
            $col = 0;

            // standard fields
            foreach ($settings as $fieldname) {
                $value = $rowdata[$fieldname];
                switch ($fieldname) {
                    case "language":
                        $worksheet->setCell($row, $col, $this->lng->txt("meta_l_" . $value));
                        break;
                    case "time_limit_from":
                    case "time_limit_until":
                        $value = $value
                            ? new ilDateTime($value, IL_CAL_UNIX)
                            : null;
                        $worksheet->setCell($row, $col, $value);
                        break;
                    case "last_login":
                    case "last_update":
                    case "create_date":
                    case "approve_date":
                    case "agree_date":
                        $value = $value
                            ? new ilDateTime($value, IL_CAL_DATETIME)
                            : null;
                        $worksheet->setCell($row, $col, $value);
                        break;
                        
                    case "interests_general":
                    case "interests_help_offered":
                    case "interests_help_looking":
                        if (is_array($value) && sizeof($value)) {
                            $value = implode(", ", $value);
                        } else {
                            $value = null;
                        }
                        // fallthrough
                        
                        // no break
                    default:
                        $worksheet->setCell($row, $col, $value);
                        break;
                }
                $col++;
            }

            // custom fields
            reset($udf_ex_fields);
            if (count($udf_ex_fields) > 0) {
                include_once("./Services/User/classes/class.ilUserDefinedData.php");
                $udf = new ilUserDefinedData($rowdata["usr_id"]);
                foreach ($udf_ex_fields as $f) {	// custom fields
                    $worksheet->setCell($row, $col, $udf->get("f_" . $f["id"]));
                    $col++;
                }
            }
        }
        
        $worksheet->writeToFile($filename);
    }

    /**
     * getExport Settings
     *
     * @return array of exportable fields
     */
    public static function getExportSettings()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $db_settings = array();
        
        include_once("./Services/User/classes/class.ilUserProfile.php");
        $up = new ilUserProfile();
        $up->skipField("roles");
        $profile_fields = $up->getStandardFields();

        /*$profile_fields =& ilObjUserFolder::getProfileFields();
        $profile_fields[] = "preferences";*/

        $query = "SELECT * FROM settings WHERE " .
            $ilDB->like("keyword", "text", '%usr_settings_export_%');
        $result = $ilDB->query($query);
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            if ($row["value"] == "1") {
                if (preg_match("/usr_settings_export_(.*)/", $row["keyword"], $setting)) {
                    array_push($db_settings, $setting[1]);
                }
            }
        }
        $export_settings = array();
        foreach ($profile_fields as $key => $value) {
            if (in_array($key, $db_settings)) {
                if (strcmp($key, "password") == 0) {
                    // we do not support password export with ILIAS >= 4.5.x
                    continue;
                } else {
                    array_push($export_settings, $key);
                }
            }
        }
        array_push($export_settings, "usr_id");
        array_push($export_settings, "login");
        array_push($export_settings, "last_login");
        array_push($export_settings, "last_update");
        array_push($export_settings, "create_date");
        array_push($export_settings, "time_limit_owner");
        array_push($export_settings, "time_limit_unlimited");
        array_push($export_settings, "time_limit_from");
        array_push($export_settings, "time_limit_until");
        array_push($export_settings, "time_limit_message");
        array_push($export_settings, "active");
        array_push($export_settings, "approve_date");
        array_push($export_settings, "agree_date");
        array_push($export_settings, "client_ip");
        array_push($export_settings, "auth_mode");
        array_push($export_settings, "ext_account");
        array_push($export_settings, "feedhash");
        return $export_settings;
    }

    /**
    * build xml export file
    */
    public function buildExportFile($a_mode = "userfolder_export_excel_x86", $user_data_filter = false)
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        global $DIC;

        $log = $DIC['log'];
        global $DIC;

        $ilDB = $DIC['ilDB'];
        global $DIC;

        $ilias = $DIC['ilias'];
        global $DIC;

        $lng = $DIC['lng'];

        //get Log File
        $expDir = $this->getExportDirectory();
        //$expLog = &$log;
        //$expLog->delete();
        //$expLog->setLogFormat("");
        //$expLog->write(date("[y-m-d H:i:s] ")."Start export of user data");

        // create export directory if needed
        $this->createExportDirectory();

        //get data
        //$expLog->write(date("[y-m-d H:i:s] ")."User data export: build an array of all user data entries");
        $settings =&$this->getExportSettings();
        
        // user languages
        $query = "SELECT * FROM usr_pref WHERE keyword = " . $ilDB->quote('language', 'text');
        $res = $ilDB->query($query);
        $languages = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $languages[$row['usr_id']] = $row['value'];
        }
        
        // multi-text
        $multi = array();
        $set = $ilDB->query("SELECT * FROM usr_data_multi");
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!is_array($user_data_filter) ||
                in_array($row["usr_id"], $user_data_filter)) {
                $multi[$row["usr_id"]][$row["field_id"]][] = $row["value"];
            }
        }
        
        $data = array();
        $query = "SELECT usr_data.* FROM usr_data  " .
            " ORDER BY usr_data.lastname, usr_data.firstname";
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            if (isset($languages[$row['usr_id']])) {
                $row['language'] = $languages[$row['usr_id']];
            } else {
                $row['language'] = $lng->getDefaultLanguage();
            }
            
            if (isset($multi[$row["usr_id"]])) {
                $row = array_merge($row, $multi[$row["usr_id"]]);
            }
            
            if (is_array($user_data_filter)) {
                if (in_array($row["usr_id"], $user_data_filter)) {
                    array_push($data, $row);
                }
            } else {
                array_push($data, $row);
            }
        }
        //$expLog->write(date("[y-m-d H:i:s] ")."User data export: build an array of all user data entries");

        $fullname = $expDir . "/" . $this->getExportFilename($a_mode);
        switch ($a_mode) {
            case "userfolder_export_excel_x86":
                $this->createExcelExport($settings, $data, $fullname);
                break;
            case "userfolder_export_csv":
                $this->createCSVExport($settings, $data, $fullname);
                break;
            case "userfolder_export_xml":
                $this->createXMLExport($settings, $data, $fullname);
                break;
        }
        //$expLog->write(date("[y-m-d H:i:s] ")."Finished export of user data");

        return $fullname;
    }


    /**
    * creates data directory for export files
    * (data_dir/usrf_data/export, depending on data
    * directory that is set in ILIAS setup/ini)
    */
    public function createExportDirectory()
    {
        if (!@is_dir($this->getExportDirectory())) {
            $usrf_data_dir = ilUtil::getDataDir() . "/usrf_data";
            ilUtil::makeDir($usrf_data_dir);
            if (!is_writable($usrf_data_dir)) {
                $this->ilias->raiseError("Userfolder data directory (" . $usrf_data_dir
                    . ") not writeable.", $this->ilias->error_obj->MESSAGE);
            }

            // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
            $export_dir = $usrf_data_dir . "/export";
            ilUtil::makeDir($export_dir);
            if (!@is_dir($export_dir)) {
                $this->ilias->raiseError("Creation of Userfolder Export Directory failed.", $this->ilias->error_obj->MESSAGE);
            }
        }
    }

    
    /**
     * Get profile fields (DEPRECATED, use ilUserProfile() instead)
     *
     * @return array of fieldnames
     */
    public static function &getProfileFields()
    {
        include_once("./Services/User/classes/class.ilUserProfile.php");
        $up = new ilUserProfile();
        $up->skipField("username");
        $up->skipField("roles");
        $up->skipGroup("preferences");
        $fds = $up->getStandardFields();
        foreach ($fds as $k => $f) {
            $profile_fields[] = $k;
        }

        return $profile_fields;
    }

    public static function _writeNewAccountMail($a_lang, $a_subject, $a_sal_g, $a_sal_f, $a_sal_m, $a_body)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (self::_lookupNewAccountMail($a_lang)) {
            $values = array(
                'subject'		=> array('text',$a_subject),
                'body'			=> array('clob',$a_body),
                'sal_g'			=> array('text',$a_sal_g),
                'sal_f'			=> array('text',$a_sal_f),
                'sal_m'			=> array('text',$a_sal_m)
                );
            $ilDB->update(
                'mail_template',
                $values,
                array('lang' => array('text',$a_lang), 'type' => array('text','nacc'))
            );
        } else {
            $values = array(
                'subject'		=> array('text',$a_subject),
                'body'			=> array('clob',$a_body),
                'sal_g'			=> array('text',$a_sal_g),
                'sal_f'			=> array('text',$a_sal_f),
                'sal_m'			=> array('text',$a_sal_m),
                'lang'			=> array('text',$a_lang),
                'type'			=> array('text','nacc')
                );
            $ilDB->insert('mail_template', $values);
        }
    }

    /**
     * Update account mail attachment
     * @param $a_lang
     * @param $a_tmp_name
     * @param $a_name
     * @throws ilFileUtilsException
     */
    public static function _updateAccountMailAttachment($a_lang, $a_tmp_name, $a_name)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once "Services/User/classes/class.ilFSStorageUserFolder.php";
        $fs = new ilFSStorageUserFolder(USER_FOLDER_ID);
        $fs->create();
        $path = $fs->getAbsolutePath() . "/";

        ilUtil::moveUploadedFile($a_tmp_name, $a_lang, $path . $a_lang);
        
        $ilDB->update(
            'mail_template',
            array('att_file' => array('text', $a_name)),
            array('lang' => array('text',$a_lang), 'type' => array('text','nacc'))
        );
    }

    /**
     * Delete account mail attachment
     * @param $a_lang
     */
    public static function _deleteAccountMailAttachment($a_lang)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once "Services/User/classes/class.ilFSStorageUserFolder.php";
        $fs = new ilFSStorageUserFolder(USER_FOLDER_ID);
        $path = $fs->getAbsolutePath() . "/";

        if (file_exists($path . $a_lang)) {
            unlink($path . $a_lang);
        }

        $ilDB->update(
            'mail_template',
            array('att_file' => array('text', '')),
            array('lang' => array('text',$a_lang), 'type' => array('text','nacc'))
        );
    }

    public static function _lookupNewAccountMail($a_lang)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query("SELECT * FROM mail_template " .
            " WHERE type='nacc' AND lang = " . $ilDB->quote($a_lang, 'text'));

        if ($rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return $rec;
        }
        return array();
    }
    
    /**
     * Update user folder assignment
     * Typically called after deleting a category with local user accounts.
     * These users will be assigned to the global user folder.
     *
     * @access public
     * @static
     *
     * @param int old_id
     * @param int new id
     */
    public static function _updateUserFolderAssignment($a_old_id, $a_new_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE usr_data SET time_limit_owner = " . $ilDB->quote($a_new_id, "integer") . " " .
            "WHERE time_limit_owner = " . $ilDB->quote($a_old_id, "integer") . " ";
        $ilDB->manipulate($query);
        
        return true;
    }
} // END class.ilObjUserFolder
