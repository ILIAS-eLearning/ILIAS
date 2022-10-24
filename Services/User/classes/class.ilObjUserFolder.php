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

/**
 * Class ilObjUserFolder
 * @author Stefan Meyer <meyer@leifos.com>
 */

class ilObjUserFolder extends ilObject
{
    public const ORG_OP_EDIT_USER_ACCOUNTS = 'edit_user_accounts';
    public const FILE_TYPE_EXCEL = 'userfolder_export_excel_x86';
    public const FILE_TYPE_CSV = 'userfolder_export_csv';
    public const FILE_TYPE_XML = 'userfolder_export_xml';

    public function __construct(
        int $a_id,
        bool $a_call_by_reference = true
    ) {
        $this->type = "usrf";
        parent::__construct($a_id, $a_call_by_reference);
    }


    public function delete(): bool
    {
        return false;
    }

    public function getExportFilename(
        string $a_mode = self::FILE_TYPE_EXCEL
    ): string {
        $filename = "";
        $inst_id = IL_INST_ID;

        $date = time();

        switch ($a_mode) {
            case self::FILE_TYPE_EXCEL:
                $filename = $date . "__" . $inst_id . "__xls_usrf";
                break;
            case self::FILE_TYPE_CSV:
                $filename = $date . "__" . $inst_id . "__csv_usrf.csv";
                break;
            case self::FILE_TYPE_XML:
                $filename = $date . "__" . $inst_id . "__xml_usrf.xml";
                break;
        }
        return $filename;
    }

    public function getExportDirectory(): string
    {
        $export_dir = ilFileUtils::getDataDir() . "/usrf_data/export";

        return $export_dir;
    }

    /**
     * Get a list of the already exported files in the export directory
     * @return array<string,string>[]
     */
    public function getExportFiles(): array
    {
        $dir = $this->getExportDirectory();

        // quit if export dir not available
        if (!is_dir($dir) or
            !is_writable($dir)) {
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
                $file[] = $filearray;
            }
        }

        // close import directory
        $dir->close();

        // sort files
        sort($file);

        return $file;
    }

    protected function escapeXML(string $value): string
    {
        $value = str_replace(["&", "<", ">"], ["&amp;", "&lt;", "&gt;"], $value);
        return $value;
    }

    protected function createXMLExport(
        array $settings,
        array $data,
        string $filename
    ): void {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilDB = $DIC['ilDB'];
        $log = $DIC['log'];

        $file = fopen($filename, 'wb');

        if (is_array($data)) {
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
    protected function getUserDefinedExportFields(): array // Missing array type.
    {
        $udf_obj = ilUserDefinedFields::_getInstance();

        $udf_ex_fields = array();
        foreach ($udf_obj->getDefinitions() as $definition) {
            if ($definition["export"] != false) {
                $udf_ex_fields[] = array("name" => $definition["field_name"],
                    "id" => $definition["field_id"]);
            }
        }

        return $udf_ex_fields;
    }

    protected function createCSVExport(
        array $settings,
        array $data,
        string $filename
    ): void {

        // header
        $headerrow = array();
        $udf_ex_fields = $this->getUserDefinedExportFields();
        foreach ($settings as $value) {	// standard fields
            $headerrow[] = $this->lng->txt($value);
        }
        foreach ($udf_ex_fields as $f) {	// custom fields
            $headerrow[] = $f["name"];
        }

        $separator = ";";
        $file = fopen($filename, 'wb');
        $formattedrow = &ilCSVUtil::processCSVRow($headerrow, true, $separator);
        fwrite($file, implode($separator, $formattedrow) . "\n");
        foreach ($data as $row) {
            $csvrow = array();
            foreach ($settings as $header) {	// standard fields
                // multi-text
                if (is_array($row[$header])) {
                    $row[$header] = implode(", ", $row[$header]);
                }

                $csvrow[] = $row[$header];
            }

            // custom fields
            reset($udf_ex_fields);
            if (count($udf_ex_fields) > 0) {
                $udf = new ilUserDefinedData($row["usr_id"]);
                foreach ($udf_ex_fields as $f) {	// custom fields
                    $csvrow[] = $udf->get("f_" . $f["id"]);
                }
            }

            $formattedrow = &ilCSVUtil::processCSVRow($csvrow, true, $separator);
            fwrite($file, implode($separator, $formattedrow) . "\n");
        }
        fclose($file);
    }

    protected function createExcelExport(
        array $settings,
        array $data,
        string $filename
    ): void {
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
        $worksheet->setBold("A1:" . $worksheet->getColumnCoord($col - 1) . "1");

        $this->lng->loadLanguageModule("meta");
        foreach ($data as $index => $rowdata) {
            $row++;
            $col = 0;

            // standard fields
            foreach ($settings as $fieldname) {
                $value = $rowdata[$fieldname] ?? "";
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
                        if (is_array($value) && count($value)) {
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
     * @return array of exportable fields
     */
    public static function getExportSettings(): array // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $db_settings = array();

        $up = new ilUserProfile();
        $up->skipField("roles");
        $profile_fields = $up->getStandardFields();

        $query = "SELECT * FROM settings WHERE " .
            $ilDB->like("keyword", "text", '%usr_settings_export_%');
        $result = $ilDB->query($query);
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            if ($row["value"] == "1") {
                if (preg_match("/usr_settings_export_(.*)/", $row["keyword"], $setting)) {
                    $db_settings[] = $setting[1];
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
                    $export_settings[] = $key;
                }
            }
        }
        $export_settings[] = "usr_id";
        $export_settings[] = "login";
        $export_settings[] = "last_login";
        $export_settings[] = "last_update";
        $export_settings[] = "create_date";
        $export_settings[] = "time_limit_owner";
        $export_settings[] = "time_limit_unlimited";
        $export_settings[] = "time_limit_from";
        $export_settings[] = "time_limit_until";
        $export_settings[] = "time_limit_message";
        $export_settings[] = "active";
        $export_settings[] = "approve_date";
        $export_settings[] = "agree_date";
        $export_settings[] = "client_ip";
        $export_settings[] = "auth_mode";
        $export_settings[] = "ext_account";
        $export_settings[] = "feedhash";
        return $export_settings;
    }

    /**
     * build xml export file
     */
    public function buildExportFile(
        string $a_mode = self::FILE_TYPE_EXCEL,
        ?array $user_data_filter = null,
        bool $use_temp_dir = false
    ): string {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        if ($use_temp_dir) {
            $expDir = ilFileUtils::ilTempnam();
            $fullname = $expDir;
        } else {
            $expDir = $this->getExportDirectory();
            // create export directory if needed
            $this->createExportDirectory();
            $fullname = $expDir . "/" . $this->getExportFilename($a_mode);
        }

        //get data
        //$expLog->write(date("[y-m-d H:i:s] ")."User data export: build an array of all user data entries");
        $settings = self::getExportSettings();

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
                    $data[] = $row;
                }
            } else {
                $data[] = $row;
            }
        }
        //$expLog->write(date("[y-m-d H:i:s] ")."User data export: build an array of all user data entries");

        switch ($a_mode) {
            case self::FILE_TYPE_EXCEL:
                $this->createExcelExport($settings, $data, $fullname);
                break;
            case self::FILE_TYPE_CSV:
                $this->createCSVExport($settings, $data, $fullname);
                break;
            case self::FILE_TYPE_XML:
                $this->createXMLExport($settings, $data, $fullname);
                break;
        }
        return $fullname;
    }


    /**
     * creates data directory for export files
     */
    protected function createExportDirectory(): void
    {
        if (!is_dir($this->getExportDirectory())) {
            $usrf_data_dir = ilFileUtils::getDataDir() . "/usrf_data";
            ilFileUtils::makeDir($usrf_data_dir);
            if (!is_writable($usrf_data_dir)) {
                $this->ilias->raiseError("Userfolder data directory (" . $usrf_data_dir
                    . ") not writeable.", $this->ilias->error_obj->MESSAGE);
            }

            // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
            $export_dir = $usrf_data_dir . "/export";
            ilFileUtils::makeDir($export_dir);
            if (!is_dir($export_dir)) {
                $this->ilias->raiseError("Creation of Userfolder Export Directory failed.", $this->ilias->error_obj->MESSAGE);
            }
        }
    }


    /**
     * Get profile fields
     * @deprecated use ilUserProfile() instead
     */
    public static function getProfileFields(): array // Missing array type.
    {
        $up = new ilUserProfile();
        $up->skipField("username");
        $up->skipField("roles");
        $up->skipGroup("preferences");
        $fds = $up->getStandardFields();
        $profile_fields = [];
        foreach ($fds as $k => $f) {
            $profile_fields[] = $k;
        }

        return $profile_fields;
    }

    public static function _writeNewAccountMail(
        string $a_lang,
        string $a_subject,
        string $a_sal_g,
        string $a_sal_f,
        string $a_sal_m,
        string $a_body
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (self::_lookupNewAccountMail($a_lang)) {
            $values = array(
                'subject' => array('text',$a_subject),
                'body' => array('clob',$a_body),
                'sal_g' => array('text',$a_sal_g),
                'sal_f' => array('text',$a_sal_f),
                'sal_m' => array('text',$a_sal_m)
                );
            $ilDB->update(
                'mail_template',
                $values,
                array('lang' => array('text',$a_lang), 'type' => array('text','nacc'))
            );
        } else {
            $values = array(
                'subject' => array('text',$a_subject),
                'body' => array('clob',$a_body),
                'sal_g' => array('text',$a_sal_g),
                'sal_f' => array('text',$a_sal_f),
                'sal_m' => array('text',$a_sal_m),
                'lang' => array('text',$a_lang),
                'type' => array('text','nacc')
                );
            $ilDB->insert('mail_template', $values);
        }
    }

    /**
     * Update account mail attachment
     * @throws ilException
     */
    public static function _updateAccountMailAttachment(
        string $a_lang,
        string $a_tmp_name,
        string $a_name
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $fs = new ilFSStorageUserFolder(USER_FOLDER_ID);
        $fs->create();
        $path = $fs->getAbsolutePath() . "/";

        ilFileUtils::moveUploadedFile($a_tmp_name, $a_lang, $path . $a_lang);

        $ilDB->update(
            'mail_template',
            array('att_file' => array('text', $a_name)),
            array('lang' => array('text',$a_lang), 'type' => array('text','nacc'))
        );
    }

    /**
     * Delete account mail attachment
     */
    public static function _deleteAccountMailAttachment(
        string $a_lang
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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

    /**
     * @param string $a_lang
     * @return array{lang: string, subject: string|null, body: string|null, salf_m: string|null sal_f: string|null, sal_g: string|null, type: string, att_file: string|null}
     */
    public static function _lookupNewAccountMail(string $a_lang): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query("SELECT * FROM mail_template " .
            " WHERE type='nacc' AND lang = " . $ilDB->quote($a_lang, 'text'));

        if ($rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return $rec;
        }

        return [];
    }

    /**
     * Update user folder assignment
     * Typically called after deleting a category with local user accounts.
     * These users will be assigned to the global user folder.
     */
    public static function _updateUserFolderAssignment(
        int $a_old_id,
        int $a_new_id
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "UPDATE usr_data SET time_limit_owner = " . $ilDB->quote($a_new_id, "integer") . " " .
            "WHERE time_limit_owner = " . $ilDB->quote($a_old_id, "integer") . " ";
        $ilDB->manipulate($query);
    }
}
