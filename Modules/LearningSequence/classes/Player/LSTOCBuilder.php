<?php

declare(strict_types=1);

use ILIAS\KioskMode\TOCBuilder;
use ILIAS\KioskMode\ControlBuilder;

/**
 * Class LSTOCBuilder
 */
class LSTOCBuilder implements TOCBuilder
{
    /**
     * @var array
     */
    protected $structure;

    /**
     * @var LSTOCBuilder|null
     */
    protected $parent;

    /**
     * @var string | null
     */
    protected $command;

    /**
     * LSControlBuilder|LSTOCBuilder 	$parent
     */
    public function __construct($parent, string $command, string $label = '', int $parameter = null, $state = null)
    {
        $this->structure = [
            'label' => $label,
            'command' => $command,
            'parameter' => $parameter,
            'state' => $state,
            'childs' => []
        ];
        $this->parent = $parent;
        $this->command = $command;
    }

    public function toJSON() : string
    {
        return json_encode($this->structure);
    }

    /**
     * @inheritdoc
     */
    public function end()
    {
        $this->parent->structure['childs'][] = $this->structure;
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function node($label, int $parameter = null, $lp = null) : TOCBuilder
    {
        //build node
        $toc = new LSTOCBuilder($this, $this->command, $label, $parameter, $lp);
        return $toc;
    }

    /**
     * @inheritdoc
     */
    public function item(string $label, int $parameter, $state = null) : TOCBuilder
    {
        $item = [
            'label' => $label,
            'command' => $this->command,
            'parameter' => $parameter,
            'state' => $state
        ];
        $this->structure['childs'][] = $item;
        return $this;
    }
}
