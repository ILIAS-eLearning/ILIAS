<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Exceptions/classes/class.ilException.php';
require_once 'class.ilSystemStyleExceptionBase.php';

/**
 * Class for advanced editing exception handling in ILIAS.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilSystemStyleException extends ilSystemStyleExceptionBase
{
    const PARSING_JSON_FAILED = 1;

    const EMPTY_ENTRY = 1001;
    const INVALID_MANDATORY_ENTRY_ATTRIBUTE = 1002;
    const DUPLICATE_ENTRY = 1003;
    const DUPLICATE_ROOT_ENTRY = 1004;
    const INVALID_ID = 1005;
    const INVALID_FILE_PATH = 1006;
    const INVALID_RULES_ENTRY = 1007;
    const INVALID_CHARACTERS_IN_ID = 1008;

    const FILE_CREATION_FAILED = 2001;
    const FOLDER_CREATION_FAILED = 2002;
    const FILE_OPENING_FAILED = 2003;
    const LESS_COMPILE_FAILED = 2004;
    const FOLDER_DELETION_FAILED = 2005;
    const FILE_DELETION_FAILED = 2006;
    const LESSC_NOT_INSTALLED = 2007;

    const SKIN_FOLDER_DOES_NOT_EXIST = 3001;
    const SKIN_CSS_DOES_NOT_EXIST = 3002;

    const NO_STYLE_ID = 5001;
    const NO_SKIN_ID = 5002;
    const NO_PARENT_STYLE = 5003;
    const NOT_EXISTING_STYLE = 5004;
    const NOT_EXISTING_SKIN = 5005;

    const SKIN_ALREADY_EXISTS = 6001;

    const SUBSTYLE_ASSIGNMENT_EXISTS = 7001;

    protected function assignMessageToCode()
    {
        switch ($this->code) {
            case self::EMPTY_ENTRY:
                $this->message = "Empty Entry " . $this->add_info;
                break;
            case self::PARSING_JSON_FAILED:
                $this->message = "Parsing JSON Failed " . $this->add_info;
                break;
            case self::INVALID_MANDATORY_ENTRY_ATTRIBUTE:
                $this->message = "Invalid mandatory entry Attribute: " . $this->add_info;
                break;
            case self::DUPLICATE_ENTRY:
                $this->message = "There are entries with the same ID. Duplicate: " . $this->add_info;
                break;
            case self::DUPLICATE_ROOT_ENTRY:
                $this->message = "There are multiple root entry. Duplicate: " . $this->add_info;
                break;
            case self::INVALID_ID:
                $this->message = "No such ID found in list or tree: " . $this->add_info;
                break;
            case self::INVALID_CHARACTERS_IN_ID:
                $this->message = "The ID given contains invalid characters: " . $this->add_info;
                break;
            case self::INVALID_FILE_PATH:
                $this->message = "Invalid file path or file not readable: " . $this->add_info;
                break;
            case self::FILE_CREATION_FAILED:
                $this->message = "File creation failed, path: " . $this->add_info;
                break;
            case self::FOLDER_CREATION_FAILED:
                $this->message = "Folder creation failed, path: " . $this->add_info;
                break;
            case self::FOLDER_DELETION_FAILED:
                $this->message = "Folder delation failed, path: " . $this->add_info;
                break;
            case self::FILE_DELETION_FAILED:
                $this->message = "File delation failed, path: " . $this->add_info;
                break;
            case self::LESS_COMPILE_FAILED:
                $this->message = "Compilation of less failed: " . $this->add_info;
                break;
            case self::FILE_OPENING_FAILED:
                $this->message = "Failed to open file  : " . $this->add_info;
                break;
            case self::SKIN_CSS_DOES_NOT_EXIST:
                $this->message = "Skin CSS does not exist: " . $this->add_info;
                break;
            case self::SKIN_FOLDER_DOES_NOT_EXIST:
                $this->message = "Skin folder does not exist: " . $this->add_info;
                break;
            case self::INVALID_RULES_ENTRY:
                $this->message = "Invalid rules entry: " . $this->add_info;
                break;
            case self::NO_STYLE_ID:
                $this->message = "No Style ID is given.";
                break;
            case self::NO_SKIN_ID:
                $this->message = "No Skin ID is given.";
                break;
            case self::NOT_EXISTING_SKIN:
                $this->message = "Skin does not exist: " . $this->add_info;
                break;
            case self::NOT_EXISTING_STYLE:
                $this->message = "Style does not exist: " . $this->add_info;
                break;
            case self::SKIN_ALREADY_EXISTS:
                $this->message = "Skin already exists: " . $this->add_info;
                break;
            case self::NO_PARENT_STYLE:
                $this->message = "No parent style defined for style: " . $this->add_info;
                break;
            case self::SUBSTYLE_ASSIGNMENT_EXISTS:
                $this->message = "The assignment of this substyle already exists: " . $this->add_info;
                break;
            case self::LESSC_NOT_INSTALLED:
                $this->message = "No less compiler is installed";
                break;
            default:
                $this->message = "Unknown Exception " . $this->add_info;
                break;
        }
    }
}
