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
/**
 * @deprecated Will be removed in the next release
 */
class ilUploadFiles
{
    public static function _getUploadDirectory(): string
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('write', SYSTEM_FOLDER_ID)) {
            return '';
        }

        $lm_set = new ilSetting("lm");
        $upload_dir = $lm_set->get("cont_upload_dir");

        $import_file_factory = new ilImportDirectoryFactory();
        try {
            $scorm_import_directory = $import_file_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_SAHS);
        } catch (InvalidArgumentException $e) {
            return '';
        }
        return $scorm_import_directory->getAbsolutePath();
    }

    /**
     * @return string[]
     */
    public static function _getUploadFiles(): array
    {
        if (!$upload_dir = self::_getUploadDirectory()) {
            return array();
        }

        // get the sorted content of the upload directory
        $handle = opendir($upload_dir);
        $files = array();
        while (false !== ($file = readdir($handle))) {
            $full_path = $upload_dir . "/" . $file;
            if (is_file($full_path) and is_readable($full_path)) {
                $files[] = $file;
            }
        }
        closedir($handle);
        sort($files);

        return $files;
    }

    public static function _checkUploadFile(string $a_file): bool
    {
        $files = self::_getUploadFiles();

        return in_array($a_file, $files);
    }

    public static function _copyUploadFile(string $a_file, string $a_target, bool $a_raise_errors = true): bool
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $lng = $DIC['lng'];
        $ilias = $DIC['ilias'];

        $file = self::_getUploadDirectory() . "/" . $a_file;

        // check if file exists
        if (!is_file($file)) {
            if ($a_raise_errors) {
                $ilias->raiseError($lng->txt("upload_error_file_not_found"), $ilias->error_obj->MESSAGE);
            } else {
                $main_tpl->setOnScreenMessage('failure', $lng->txt("upload_error_file_not_found"), true);
            }
            return false;
        }

        // virus handling
        $vir = ilVirusScanner::virusHandling($file, $a_file);
        if (!$vir[0]) {
            if ($a_raise_errors) {
                $ilias->raiseError(
                    $lng->txt("file_is_infected") . "<br />" .
                    $vir[1],
                    $ilias->error_obj->MESSAGE
                );
            } else {
                $main_tpl->setOnScreenMessage('failure', $lng->txt("file_is_infected") . "<br />" .
                    $vir[1], true);
            }
            return false;
        } else {
            if ($vir[1] != "") {
                $main_tpl->setOnScreenMessage('info', $vir[1], true);
            }
            return copy($file, $a_target);
        }
    }
}
