<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Menu\Drilldown as DrilldownMenu;

/**
 * Drilldown Slate
 */
class Drilldown extends Slate implements ISlate\Drilldown
{
    protected DrilldownMenu $drilldown;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $name,
        Symbol $symbol,
        DrilldownMenu $drilldown
    ) {
        parent::__construct($signal_generator, $name, $symbol);
        $this->drilldown = $drilldown;
    }

    public function getContents() : array
    {
        return [$this->drilldown];
    }

    public function withMappedSubNodes(callable $f) : self
    {
        return $this;
    }
}
