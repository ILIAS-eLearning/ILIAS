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

namespace ILIAS\UI\Implementation\Component\Chart;

use ILIAS\UI\Component as C;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing
 */
class Factory implements C\Chart\Factory
{
    protected C\Chart\ProgressMeter\Factory $progressmeter_factory;
    protected C\Chart\Bar\Factory $bar_factory;

    public function __construct(
        C\Chart\ProgressMeter\Factory $progressmeter_factory,
        C\Chart\Bar\Factory $bar_factory
    ) {
        $this->progressmeter_factory = $progressmeter_factory;
        $this->bar_factory = $bar_factory;
    }

    /**
     * @inheritdoc
     */
    public function scaleBar(array $items): C\Chart\ScaleBar
    {
        return new ScaleBar($items);
    }

    /**
     * @inheritdoc
     */
    public function progressMeter(): C\Chart\ProgressMeter\Factory
    {
        return $this->progressmeter_factory;
    }

    /**
     * @inheritdoc
     */
    public function bar(): C\Chart\Bar\Factory
    {
        return $this->bar_factory;
    }
}
