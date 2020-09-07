<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Exceptions/classes/class.ilException.php';

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclInputException extends ilException
{
    const TYPE_EXCEPTION = 0;
    const LENGTH_EXCEPTION = 1;
    const REGEX_EXCEPTION = 2;
    const UNIQUE_EXCEPTION = 3;
    const NOT_URL = 4;
    const NOT_IMAGE = 5;
    const WRONG_FILE_TYPE = 6;
    const CUSTOM_MESSAGE = 7;
    const REGEX_CONFIG_EXCEPTION = 8;


    /**
     * @var int
     */
    protected $exception_type;

    /**
     * @var string
     */
    protected $additional_text;


    /**
     * @param string $exception_type
     */
    public function __construct($exception_type, $additional_text = "")
    {
        parent::__construct($exception_type);
        $this->exception_type = $exception_type;
        $this->additional_text = $additional_text;
    }


    /**
     * @return string
     */
    public function getExceptionType()
    {
        return $this->exception_type;
    }


    /**
     * @return string
     */
    public function __toString()
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
