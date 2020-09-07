<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Exceptions/classes/class.ilException.php';

/**
 * Class for advanced editing exception handling in ILIAS.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
abstract class ilSystemStyleExceptionBase extends ilException
{
    const UNKNONW_EXCEPTION = -1;

    /**
     * @var string
     */
    protected $message = "";

    /**
     * @var int
     */
    protected $code = -1;

    /**
     * @var string
     */
    protected $add_info = "";

    /**
     * ilSystemStyleException constructor.
     * @param int $exception_code
     * @param string $exception_info
     */
    public function __construct($exception_code = -1, $exception_info = "")
    {
        $this->code = $exception_code;
        $this->add_info = $exception_info;
        $this->assignMessageToCode();
        parent::__construct($this->message, $this->code);
    }

    abstract protected function assignMessageToCode();

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
        . "{$this->getTraceAsString()}";
    }
}
