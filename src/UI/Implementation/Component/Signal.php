<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component;

/**
 * Class Signal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class Signal implements \ILIAS\UI\Component\Signal
{
    protected string $id;
    protected array $options = array();

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId() : string
    {
        return $this->id;
    }

    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * @param mixed $value
     */
    public function addOption(string $key, $value) : void
    {
        $this->options[$key] = $value;
    }

    /**
     * @return mixed|null
     */
    protected function getOption(string $key)
    {
        return (isset($this->options[$key])) ? $this->options[$key] : null;
    }

    public function __toString() : string
    {
        return $this->id;
    }
}
