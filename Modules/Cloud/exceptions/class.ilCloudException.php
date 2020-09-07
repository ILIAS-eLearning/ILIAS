<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Exceptions/classes/class.ilException.php';

/**
 * Class ilCloudException
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id:
 * @extends ilException
 * @ingroup ModulesCloud
 */
class ilCloudException extends ilException
{
    const UNKNONW_EXCEPTION = -1;

    const NO_SERVICE_ACTIVE = 1001;
    const NO_SERVICE_SELECTED = 1002;
    const SERVICE_NOT_ACTIVE = 1003;
    const SERVICE_CLASS_FILE_NOT_FOUND = 1004;
    const PLUGIN_HOOK_COULD_NOT_BE_INSTANTIATED = 1005;

    const FOLDER_NOT_EXISTING_ON_SERVICE = 1101;
    const FILE_NOT_EXISTING_ON_SERVICE = 1102;
    const FOLDER_ALREADY_EXISTING_ON_SERVICE = 1103;

    const AUTHENTICATION_FAILED = 2001;
    const DELETE_FAILED = 2101;
    const DOWNLOAD_FAILED = 2201;
    const FOLDER_CREATION_FAILED = 2301;
    const UPLOAD_FAILED = 2401;
    const UPLOAD_FAILED_MAX_FILESIZE = 2402;
    const ADD_ITEMS_FROM_SERVICE_FAILED = 2501;

    const INVALID_INPUT = 3001;

    const PATH_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION = 4001;
    const ID_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION = 4002;
    const ID_ALREADY_EXISTS_IN_FILE_TREE_IN_SESSION = 4003;

    const PERMISSION_DENIED = 5001;
    const PERMISSION_TO_CHANGE_ROOT_FOLDER_DENIED = 5002;

    protected $message;
    protected $code;
    protected $add_info;

    /**
     * @param string $exception_code
     * @param string $exception_info
     */
    public function __construct($exception_code, $exception_info = "")
    {
        $this->code = $exception_code;
        $this->add_info = $exception_info;
        $this->assignMessageToCode();
        parent::__construct($this->message, $this->code);
    }

    protected function assignMessageToCode()
    {
        global $DIC;
        $lng = $DIC['lng'];
        switch ($this->code) {
            case self::NO_SERVICE_ACTIVE:
                $this->message = $lng->txt("cld_no_service_active");
                break;
            case self::NO_SERVICE_SELECTED:
                $this->message = $lng->txt("cld_no_service_selected");
                break;
            case self::SERVICE_NOT_ACTIVE:
                $this->message = $lng->txt("cld_service_not_active");
                break;
            case self::SERVICE_CLASS_FILE_NOT_FOUND:
                $this->message = $lng->txt("cld_service_class_file_not_found");
                break;
            case self::FOLDER_NOT_EXISTING_ON_SERVICE:
                $this->message = $lng->txt("cld_folder_not_existing_on_service");
                break;
            case self::FOLDER_ALREADY_EXISTING_ON_SERVICE:
                $this->message = $lng->txt("cld_folder_already_existing_on_service");
                break;
            case self::FILE_NOT_EXISTING_ON_SERVICE:
                $this->message = $lng->txt("cld_file_not_existing_on_service");
                break;
            case self::AUTHENTICATION_FAILED:
                $this->message = $lng->txt("cld_authentication_failed");
                break;
            case self::DELETE_FAILED:
                $this->message = $lng->txt("cld_delete_failed");
                break;
            case self::ADD_ITEMS_FROM_SERVICE_FAILED:
                $this->message = $lng->txt("cld_add_items_from_service_failed");
                break;
            case self::DOWNLOAD_FAILED:
                $this->message = $lng->txt("cld_add_download_failed");
                break;
            case self::FOLDER_CREATION_FAILED:
                $this->message = $lng->txt("cld_folder_creation_failed");
                break;
            case self::UPLOAD_FAILED:
                $this->message = $lng->txt("cld_upload_failed");
                break;
            case self::UPLOAD_FAILED_MAX_FILESIZE:
                $this->message = $lng->txt("cld_upload_failed_max_filesize");
                break;
            case self::INVALID_INPUT:
                $this->message = $lng->txt("cld_invalid_input");
                break;
            case self::PATH_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION:
                $this->message = $lng->txt("cld_path_does_not_exist_in_file_tree_in_session");
                break;
            case self::ID_DOES_NOT_EXIST_IN_FILE_TREE_IN_SESSION:
                $this->message = $lng->txt("cld_id_does_not_exist_in_file_tree_in_session");
                break;
            case self::ID_ALREADY_EXISTS_IN_FILE_TREE_IN_SESSION:
                $this->message = $lng->txt("cld_id_already_exists_in_file_tree_in_session");
                break;
            case self::PLUGIN_HOOK_COULD_NOT_BE_INSTANTIATED:
                $this->message = $lng->txt("cld_plugin_hook_could_not_be_instantiated");
                break;
            case self::PERMISSION_DENIED:
                $this->message = $lng->txt("cld_permission_denied");
                break;
            case self::PERMISSION_TO_CHANGE_ROOT_FOLDER_DENIED:
                $this->message = $lng->txt("cld_permission_to_change_root_folder_denied");
                break;
            default:
                $this->message = $lng->txt("cld_unknown_exception");
                break;
        }
        $this->message .= ($this->add_info ? ": " : "") . $this->add_info;
    }

    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
            . "{$this->getTraceAsString()}";
    }
}
