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

namespace ILIAS\UI\Implementation\Component\Menu\Drilldown;

use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements IMenu\Drilldown\Factory
{
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function standard(string $label, array $items): IMenu\Drilldown\Standard
    {
        return new Standard($this->signal_generator, $label, $items);
    }

    /**
     * @inheritdoc
     */
    public function categorisedItems(string $label, array $items): IMenu\Drilldown\CategorisedItems
    {
        return new CategorisedItems($this->signal_generator, $label, $items);
    }
}
