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

namespace ILIAS\UI\Implementation\Component\Dropdown;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\Link;

/**
 * This implements commonalities between different types of Dropdowns.
 */
abstract class Dropdown implements C\Dropdown\Dropdown
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    protected ?string $label = null;
    protected ?string $aria_label = null;

    /**
     * @var array<Shy|Horizontal|Link\Standard>
     */
    protected array $items;

    /**
     * Dropdown constructor.
     * @param array<Shy|Horizontal|Link\Standard> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getAriaLabel(): ?string
    {
        return $this->aria_label;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function withLabel(string $label): C\Dropdown\Dropdown
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAriaLabel(string $label): C\Dropdown\Dropdown
    {
        $clone = clone $this;
        $clone->aria_label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOnClick(Signal $signal): C\Clickable
    {
        return $this->withTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function appendOnClick(Signal $signal): C\Clickable
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function withOnHover(Signal $signal): C\Hoverable
    {
        return $this->withTriggeredSignal($signal, 'hover');
    }

    /**
     * @inheritdoc
     */
    public function appendOnHover(Signal $signal): C\Hoverable
    {
        return $this->appendTriggeredSignal($signal, 'hover');
    }
}
