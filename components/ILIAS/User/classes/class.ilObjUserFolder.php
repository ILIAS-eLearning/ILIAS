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

declare(strict_types=1);

use ILIAS\User\LocalDIC;
use ILIAS\User\BuildExportFieldArray;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Profile\Fields\Standard\Roles;
use ILIAS\User\Profile\Fields\Standard\Alias;
use ILIAS\User\Settings\Settings;
use ILIAS\User\Settings\SettingsImplementation;

/**
 * Class ilObjUserFolder
 * @author Stefan Meyer <meyer@leifos.com>
 */

class ilObjUserFolder extends ilObject
{
    use BuildExportFieldArray;

    public const string PERM_READ_ALL = 'read_all_accounts';
    public const string PERM_READ_ALL_AND_WRITE = 'read_all_accounts,write';

    public const ORG_OP_EDIT_USER_ACCOUNTS = 'edit_user_accounts';
    public const FILE_TYPE_EXCEL = 'userfolder_export_excel_x86';
    public const FILE_TYPE_CSV = 'userfolder_export_csv';
    public const FILE_TYPE_XML = 'userfolder_export_xml';

    private Profile $profile;
    private SettingsImplementation $settings;

    public function __construct(
        int $a_id,
        bool $a_call_by_reference = true
    ) {
        $this->type = 'usrf';
        parent::__construct($a_id, $a_call_by_reference);

        $this->profile = LocalDIC::dic()[Profile::class];
        $this->settings = LocalDIC::dic()[Settings::class];
    }


    public function delete(): bool
    {
        return false;
    }

    public function getExportFilename(
        string $a_mode = self::FILE_TYPE_EXCEL
    ): string {
        $filename = '';
        $inst_id = IL_INST_ID;

        $date = time();

        switch ($a_mode) {
            case self::FILE_TYPE_EXCEL:
                $filename = $date . '__' . $inst_id . '__xls_usrf';
                break;
            case self::FILE_TYPE_CSV:
                $filename = $date . '__' . $inst_id . '__csv_usrf.csv';
                break;
            case self::FILE_TYPE_XML:
                $filename = $date . '__' . $inst_id . '__xml_usrf.xml';
                break;
        }
        return $filename;
    }

    public function getExportDirectory(): string
    {
        $export_dir = ilFileUtils::getDataDir() . '/usrf_data/export';

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
        if (!is_dir($dir)
            || !is_writable($dir)) {
            return [];
        }

        // open directory
        $dir = dir($dir);

        // initialize array
        $file = [];

        // get files and save the in the array
        while ($entry = $dir->read()) {
            if ($entry !== '.'
                && $entry !== '..'
                && preg_match('/^[0-9]{10}_{2}[0-9]+_{2}([a-z0-9]{3})_usrf\.[a-z]{1,4}$/', $entry, $matches)) {
                $filearray['filename'] = $entry;
                $filearray['filesize'] = filesize($this->getExportDirectory() . '/' . $entry);
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
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $value);
    }

    protected function createXMLExport(
        array $fields_to_export,
        array $data,
        string $filename
    ): void {
        $xml_writer = new ilUserXMLWriter();
        $xml_writer->setObjects($data);
        $xml_writer->setFieldsToExport(
            array_merge(array_keys($fields_to_export), ['time_limit_owner'])
        );
        $xml_writer->setAttachRoles(true);

        if ($xml_writer->start()) {
            fwrite(fopen($filename, 'wb'), $xml_writer->getXML());
        }
    }

    protected function createCSVExport(
        array $fields_to_export,
        array $data,
        string $filename
    ): void {
        $headerrow = [];
        $udf_ex_fields = $this->getUserDefinedExportFields();
        foreach ($fields_to_export as $value) {	// standard fields
            $headerrow[] = $this->lng->txt($value);
        }
        foreach ($udf_ex_fields as $f) {	// custom fields
            $headerrow[] = $f['name'];
        }

        $file = fopen($filename, 'wb');
        fwrite($file, $this->processCSVRow($headerrow) . "\n");
        foreach ($data as $row) {
            $csvrow = [];
            foreach ($settings as $header) {	// standard fields
                // multi-text
                if (isset($row[$header]) && is_array($row[$header])) {
                    $row[$header] = implode(', ', $row[$header]);
                }

                $csvrow[] = $row[$header] ?? '';
            }

            // custom fields
            reset($udf_ex_fields);
            if (count($udf_ex_fields) > 0) {
                $udf = $this->profile->getDataFor($row['usr_id']);
                foreach ($udf_ex_fields as $f) {	// custom fields
                    $csvrow[] = $udf->get('f_' . $f['id']);
                }
            }

            fwrite($file, $this->processCSVRow($csvrow) . "\n");
        }
        fclose($file);
    }

    protected function createExcelExport(
        array $fields_to_export,
        array $data,
        string $filename
    ): void {
        $worksheet = new ilExcel();
        $worksheet->addSheet($this->lng->txt('users'));

        $row = 1;
        $col = 0;

        // title row
        foreach ($fields_to_export as $label) {	// standard fields
            $worksheet->setCell($row, $col, $label);
            $col++;
        }
        $worksheet->setBold('A1:' . $worksheet->getColumnCoord($col - 1) . '1');

        $this->lng->loadLanguageModule('meta');
        foreach ($data as $rowdata) {
            $row++;
            $col = 0;

            // standard fields
            foreach (array_keys($fields_to_export) as $fieldname) {
                $value = $rowdata[$fieldname] ?? '';
                switch ($fieldname) {
                    case 'language':
                        $worksheet->setCell($row, $col, $this->lng->txt('meta_l_' . $value));
                        break;
                    case 'time_limit_from':
                    case 'time_limit_until':
                        $value = $value
                            ? new ilDateTime($value, IL_CAL_UNIX)
                            : null;
                        $worksheet->setCell($row, $col, $value);
                        break;
                    case 'last_login':
                    case 'last_update':
                    case 'create_date':
                    case 'approve_date':
                    case 'agree_date':
                        $value = $value
                            ? new ilDateTime($value, IL_CAL_DATETIME)
                            : null;
                        $worksheet->setCell($row, $col, $value);
                        break;

                    default:
                        $worksheet->setCell(
                            $row,
                            $col,
                            is_array($value) && $value !== []
                                ? implode(', ', $value)
                                : $value
                        );
                }
                $col++;
            }
        }

        $worksheet->writeToFile($filename);
    }

    /**
     * build xml export file
     */
    public function buildExportFile(
        string $a_mode = self::FILE_TYPE_EXCEL,
        ?array $user_data_filter = null,
        bool $use_temp_dir = false
    ): string {
        if ($use_temp_dir) {
            $export_dir = ilFileUtils::ilTempnam();
            $fullname = $export_dir;
        } else {
            $export_dir = $this->getExportDirectory();
            // create export directory if needed
            $this->createExportDirectory();
            $fullname = $export_dir . '/' . $this->getExportFilename($a_mode);
        }

        $fields_to_export = $this->getExportFieldArray(
            $this->lng,
            $this->profile,
            $this->settings
        );
        $data = $this->retrieveExportDataArray(
            $this->buildWhereForUserDataFilterArray($user_data_filter ?? [])
        );

        switch ($a_mode) {
            case self::FILE_TYPE_EXCEL:
                $this->createExcelExport($fields_to_export, $data, $fullname);
                break;
            case self::FILE_TYPE_CSV:
                $this->createCSVExport($fields_to_export, $data, $fullname);
                break;
            case self::FILE_TYPE_XML:
                $this->createXMLExport($fields_to_export, $data, $fullname);
                break;
        }
        return $fullname;
    }

    private function processCSVRow(array $row): array
    {
        $resultarray = [];
        foreach ($row as $rowindex => $entry) {
            $resultarray[$rowindex] = iconv(
                'UTF-8',
                'ISO-8859-1',
                '"' . str_replace(chr(13) . chr(10), chr(10), $entry) . '"'
            );
        }
        return implode(';', $resultarray);
    }

    private function retrieveExportDataArray(string $usr_ids_where): array
    {
        $query = "SELECT * FROM usr_pref WHERE keyword = {$this->db->quote('language', 'text')}";
        if ($usr_ids_where !== '') {
            $query .= "AND {$usr_ids_where}";
        }
        $res = $this->db->query($query);
        $languages = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $languages[$row['usr_id']] = $row['value'];
        }

        // multi-text
        $multi = $this->retrieveMultiRowDataArray($usr_ids_where);

        $query = 'SELECT usr_data.* FROM usr_data  ';
        if ($usr_ids_where !== '') {
            $query .= "WHERE {$usr_ids_where} ";
        }
        $set = $this->db->query("{$query} ORDER BY usr_data.lastname, usr_data.firstname");

        $data = [];
        while ($row = $this->db->fetchAssoc($set)) {
            $row['language'] = $languages[$row['usr_id']] ?? $this->lng->getDefaultLanguage();
            $data[] = array_merge($row, $multi[$row['usr_id']] ?? []);
        }

        return $data;
    }

    private function retrieveMultiRowDataArray(string $usr_ids_where): array
    {
        $query = 'SELECT * FROM usr_profile_data';
        if ($usr_ids_where !== '') {
            $query .= " WHERE {$usr_ids_where}";
        }
        $set = $this->db->query($query);
        $multi = [];
        while ($row = $this->db->fetchAssoc($set)) {
            $multi[$row['usr_id']][$row['field_id']][] = $row['value'];
        }
        return $multi;
    }

    private function buildWhereForUserDataFilterArray(array $user_data_filter): string
    {
        if ($user_data_filter === []) {
            return '';
        }

        return $this->db->in('usr_id', $user_data_filter, false, ilDBConstants::T_INTEGER);
    }


    /**
     * creates data directory for export files
     */
    private function createExportDirectory(): void
    {
        if (!is_dir($this->getExportDirectory())) {
            $usrf_data_dir = ilFileUtils::getDataDir() . '/usrf_data';
            ilFileUtils::makeDir($usrf_data_dir);
            if (!is_writable($usrf_data_dir)) {
                $this->ilias->raiseError('Userfolder data directory (' . $usrf_data_dir
                    . ') not writeable.', $this->ilias->error_obj->MESSAGE);
            }

            // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
            $export_dir = $usrf_data_dir . '/export';
            ilFileUtils::makeDir($export_dir);
            if (!is_dir($export_dir)) {
                $this->ilias->raiseError('Creation of Userfolder Export Directory failed.', $this->ilias->error_obj->MESSAGE);
            }
        }
    }


    /**
     * Get profile fields
     * @deprecated use ilUserProfile() instead
     */
    public static function getProfileFields(): array // Missing array type.
    {
        return array_key(LocalDIC::dic()[Profile::class]->getFields(
            [],
            [Alias::class, Roles::class]
        ));
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

        $query = 'UPDATE usr_data SET time_limit_owner = ' . $ilDB->quote($a_new_id, ilDBConstants::T_INTEGER) . ' ' .
            'WHERE time_limit_owner = ' . $ilDB->quote($a_old_id, ilDBConstants::T_INTEGER) . ' ';
        $ilDB->manipulate($query);
    }
}
