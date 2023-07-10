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

    /**
     * @inheritdoc
     */
    public function getContents(): array
    {
        return [$this->drilldown];
    }

    public function withMappedSubNodes(callable $f): self
    {
        return $this;
    }
}
