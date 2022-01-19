<?php

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

    abstract protected function assignMessageToCode(): void;

    public function __toString(): string
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
            . "{$this->getTraceAsString()}";
    }
}
