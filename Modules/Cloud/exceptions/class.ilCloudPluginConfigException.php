<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilCloudException.php';

/**
 * Class ilCloudPluginConfigException
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id:
 * @extends ilCloudException
 * @ingroup ModulesCloud
 */
class ilCloudPluginConfigException extends ilCloudException
{
    const TABLE_DOES_NOT_EXIST                  = 100001;
    const ENTRY_DOES_NOT_EXIST                  = 100002;
    const NO_VALID_GET_OR_SET_FUNCTION          = 100002;

    protected function assignMessageToCode()
    {
        global $lng;
        switch ($this->code)
        {
            case self::TABLE_DOES_NOT_EXIST:
                $this->message = $lng->txt("cld_config_table_does_not_exist") . " " . $this->add_info;
                break;
            case self::ENTRY_DOES_NOT_EXIST:
                $this->message = $lng->txt("cld_config_entry_does_not_exist") . " " . $this->add_info;
                break;
            case self::NO_VALID_GET_OR_SET_FUNCTION:
                $this->message = $lng->txt("cld_config_no_valid_get_or_set_function") . " " . $this->add_info;
                break;
            default:
                $this->message = $lng->txt("cld_config_unknown_exception") . " " . $this->add_info;
                break;
        }
    }
}