<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\KioskMode\TOCBuilder;

/**
 * Class LSTOCBuilder
 */
class LSTOCBuilder implements TOCBuilder
{
    /**
     * @var array<string, mixed>
     */
    protected array $structure;

    /**
     * @var LSControlBuilder|LSTOCBuilder|null
     */
    protected $parent;

    protected ?string $command;

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
    public function node(string $label, int $parameter = null, int $lp = null) : TOCBuilder
    {
        //build node
        $toc = new LSTOCBuilder($this, $this->command, $label, $parameter, $lp);
        return $toc;
    }

    /**
     * @inheritdoc
     */
    public function item(string $label, int $parameter, $state = null, bool $current = false) : TOCBuilder
    {
        $item = [
            'label' => $label,
            'command' => $this->command,
            'parameter' => $parameter,
            'state' => $state,
            'current' => $current
        ];
        $this->structure['childs'][] = $item;
        return $this;
    }
}
