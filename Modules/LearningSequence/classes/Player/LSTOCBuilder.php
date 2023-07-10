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

    public function toJSON(): string
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
    public function node(string $label, int $parameter = null, int $lp = null): TOCBuilder
    {
        //build node
        $toc = new LSTOCBuilder($this, $this->command, $label, $parameter, $lp);
        return $toc;
    }

    /**
     * @inheritdoc
     */
    public function item(string $label, int $parameter, $state = null, bool $current = false): TOCBuilder
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
