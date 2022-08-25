<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 ********************************************************************
 */

/**
 * Class ilDclBaseFieldModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclInputException extends ilException
{
    public const TYPE_EXCEPTION = 0;
    public const LENGTH_EXCEPTION = 1;
    public const REGEX_EXCEPTION = 2;
    public const UNIQUE_EXCEPTION = 3;
    public const NOT_URL = 4;
    public const NOT_IMAGE = 5;
    public const WRONG_FILE_TYPE = 6;
    public const CUSTOM_MESSAGE = 7;
    public const REGEX_CONFIG_EXCEPTION = 8;

    protected string $exception_type;
    protected string $additional_text;

    /**
     * @param string $exception_type
     */
    public function __construct($exception_type, $additional_text = "")
    {
        parent::__construct($exception_type);
        $this->exception_type = $exception_type;
        $this->additional_text = $additional_text;
    }

    public function getExceptionType(): string
    {
        return $this->exception_type;
    }

    public function __toString(): string
    {
        global $DIC;
        $lng = $DIC['lng'];

        switch ($this->exception_type) {
            case self::TYPE_EXCEPTION:
                $message = $lng->txt('dcl_wrong_input_type');
                break;
            case self::LENGTH_EXCEPTION:
                $message = $lng->txt('dcl_wrong_length');
                break;
            case self::REGEX_EXCEPTION:
                $message = $lng->txt('dcl_wrong_regex');
                break;
            case self::REGEX_CONFIG_EXCEPTION:
                $message = $lng->txt('dcl_invalid_regex_config');
                break;
            case self::UNIQUE_EXCEPTION:
                $message = $lng->txt('dcl_unique_exception');
                break;
            case self::NOT_URL:
                $message = $lng->txt('dcl_noturl_exception');
                break;
            case self::NOT_IMAGE:
                $message = $lng->txt('dcl_notimage_exception');
                break;
            case self::WRONG_FILE_TYPE:
                $message = $lng->txt('dcl_not_supported_file_type');
                break;
            case self::CUSTOM_MESSAGE:
                return $this->additional_text;
                break;
            default:
                $message = $lng->txt('dcl_unknown_exception');
        }

        if (strlen($this->additional_text) > 0) {
            $message .= " " . $this->additional_text;
        }

        return $message;
    }
}
