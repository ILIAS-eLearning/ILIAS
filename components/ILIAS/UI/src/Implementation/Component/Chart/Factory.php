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

namespace ILIAS\UI\Implementation\Component\Chart;

use ILIAS\UI\Component as C;

class Factory implements C\Chart\Factory
{
    protected ProgressMeter\Factory $progressmeter_factory;
    protected Bar\Factory $bar_factory;

    public function __construct(
        ProgressMeter\Factory $progressmeter_factory,
        Bar\Factory $bar_factory
    ) {
        $this->progressmeter_factory = $progressmeter_factory;
        $this->bar_factory = $bar_factory;
    }

    public function scaleBar(array $items): ScaleBar
    {
        return new ScaleBar($items);
    }

    public function progressMeter(): ProgressMeter\Factory
    {
        return $this->progressmeter_factory;
    }

    public function bar(): Bar\Factory
    {
        return $this->bar_factory;
    }
}
