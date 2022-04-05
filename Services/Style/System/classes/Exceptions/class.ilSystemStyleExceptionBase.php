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

/**
 * Class for advanced editing exception handling in ILIAS.
 */
abstract class ilSystemStyleExceptionBase extends ilException
{
    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var int
     */
    protected $code = -1;

    protected string $add_info = '';

    public function __construct(int $exception_code = -1, string $exception_info = '')
    {
        $this->code = $exception_code;
        $this->add_info = $exception_info;
        $this->assignMessageToCode();
        parent::__construct($this->message, $this->code);
    }

    abstract protected function assignMessageToCode() : void;

    public function __toString() : string
    {
        return get_class($this) . " '$this->message' in $this->file($this->line)\n"
            . "{$this->getTraceAsString()}";
    }
}
