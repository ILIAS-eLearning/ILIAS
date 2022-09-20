<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * Exceptions for the Crawler to parse Metadata from the UI Components from YAML to PHP
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class CrawlerException extends \Exception
{
    public const UNKNOWN_EXCEPTION = -1;

    public const ARRAY_EXPECTED = 1000;
    public const STRING_EXPECTED = 1001;
    public const INVALID_TYPE = 1002;
    public const EMPTY_STRING = 1002;

    public const EMPTY_ENTRY = 2000;
    public const INVALID_MANDATORY_ENTRY_ATTRIBUTE = 2001;
    public const DUPLICATE_ENTRY = 2002;
    public const DUPLICATE_ROOT_ENTRY = 2003;
    public const INVALID_ID = 2004;
    public const INVALID_FILE_PATH = 2005;
    public const INVALID_RULES_ENTRY = 2006;
    public const ENTRY_WITH_NO_YAML_DESCRIPTION = 2007;
    public const ENTRY_WITH_NO_VALID_RETURN_STATEMENT = 2008;
    public const PARSING_YAML_ENTRY_FAILED = 2009;
    public const ENTRY_TITLE_MISSING = 2010;
    public const ENTRY_WITHOUT_FUNCTION = 2011;

    public const FILE_CREATION_FAILED = 3000;
    public const FOLDER_CREATION_FAILED = 3001;
    public const FILE_OPENING_FAILED = 3002;
    public const LESS_COMPILE_FAILED = 3003;
    public const FOLDER_DELETION_FAILED = 3004;
    public const FILE_DELETION_FAILED = 3005;

    public const INVALID_INDEX = 4000;
    public const MISSING_INDEX = 4001;

    public const CRAWL_MAX_NESTING_REACHED = 5000;

    protected string $add_info = "";

    /**
     * ilKitchenSinkException constructor.
     */
    public function __construct(int $exception_code = -1, string $exception_info = "")
    {
        $this->add_info = $exception_info;
        $this->code = $exception_code;
        $this->assignMessageToCode();
        parent::__construct($this->message, $exception_code);
    }

    protected function assignMessageToCode(): void
    {
        switch ($this->code) {
            case self::ARRAY_EXPECTED:
                $this->message = "Array was expected, got " . $this->add_info;
                break;
            case self::STRING_EXPECTED:
                $this->message = "String was expected, got " . $this->add_info;
                break;
            case self::INVALID_TYPE:
                $this->message = "Invalid type: " . $this->add_info;
                break;
            case self::EMPTY_STRING:
                $this->message = "String can not be empty: " . $this->add_info;
                break;

            case self::EMPTY_ENTRY:
                $this->message = "Empty Entry " . $this->add_info;
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
                $this->message = "No such ID in tree: " . $this->add_info;
                break;
            case self::ENTRY_WITH_NO_YAML_DESCRIPTION:
                $this->message = "No YAML Description found for Entry returned by: '" . $this->add_info .
                    "' (check if the entry is properly introduced and closed with '---' before return statement)";
                break;
            case self::ENTRY_WITH_NO_VALID_RETURN_STATEMENT:
                $this->message = "No Return statement given for Entry: " . $this->add_info;
                break;
            case self::PARSING_YAML_ENTRY_FAILED:
                $this->message = "Parsing Yaml entry failed: " . $this->add_info;
                break;
            case self::ENTRY_TITLE_MISSING:
                $this->message = "Entry Title missing (check if valid function name is set for all entries): " . $this->add_info;
                break;
            case self::ENTRY_WITHOUT_FUNCTION:
                $this->message = "Entry Function missing: " . $this->add_info;
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

            case self::INVALID_INDEX:
                $this->message = "Invalid index: " . $this->add_info;
                break;
            case self::MISSING_INDEX:
                $this->message = "Missing index in class: " . $this->add_info;
                break;
            case self::CRAWL_MAX_NESTING_REACHED:
                $this->message = "Max nesting reached while crowling (Factories might contain a circle), info: " . $this->add_info;
                break;

            case self::UNKNOWN_EXCEPTION:
            default:
                $this->message = "Unknown Exception " . $this->add_info;
                break;
        }
    }

    public function __toString(): string
    {
        return
            get_class($this) .
            " '$this->message' in $this->file($this->line)\n" .
            "{$this->getTraceAsString()}"
        ;
    }
}
