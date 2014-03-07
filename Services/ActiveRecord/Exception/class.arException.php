<?php

/**
 * Class arException
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class arException extends Exception {
    const UNKNONWN_EXCEPTION = -1;
    const COLUMN_DOES_NOT_EXIST                     = 1001;
    const COLUMN_DOES_ALREADY_EXIST                 = 1002;

    /**
     * @var string
     */
    protected $message  = "";

    /**
     * @var int
     */
    protected $code = 0;

    /**
     * @var string
     */
    protected $add_info = "";

    /**
     * @param string $exception_code
     * @param string $exception_info
     */
    public function __construct($exception_info = "", $exception_code = -1)
    {


        $this->code     = $exception_code;
        $this->add_info = $exception_info;
        $this->assignMessageToCode();
        parent::__construct($this->message, $this->code);

    }

    protected function assignMessageToCode()
    {
        global $lng;
        switch ($this->code)
        {
            case self::COLUMN_DOES_NOT_EXIST:
                $this->message = $lng->txt("Active record Exception: Column does not exist, ") . " " . $this->add_info;
                break;
            case self::COLUMN_DOES_ALREADY_EXIST:
                $this->message = $lng->txt("Active record Exception: Column does already exist, ") . " " . $this->add_info;
                break;
            default:
                $this->message = $lng->txt("Active record Exception: Unknown Exception") . " " . $this->add_info;
                break;
        }
    }

    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
        . "{$this->getTraceAsString()}";
    }
}

?>
