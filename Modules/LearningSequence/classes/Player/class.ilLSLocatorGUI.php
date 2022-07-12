<?php declare(strict_types=1);

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
 
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;

/**
 * GUI for Locator element
 */
class ilLSLocatorGUI
{
    protected LSUrlBuilder $url_builder;
    protected Factory $ui_factory;
    protected array $items;

    public function __construct(LSUrlBuilder $url_builder, Factory $ui_factory)
    {
        $this->url_builder = $url_builder;
        $this->ui_factory = $ui_factory;
    }

    public function withItems(array $items) : ilLSLocatorGUI
    {
        $clone = clone $this;
        $clone->items = $items;
        return $clone;
    }

    public function getComponent() : Component
    {
        $crumbs = array_map(
            function ($item) {
                return $this->ui_factory->link()->standard(
                    $item['label'],
                    $this->url_builder->getHref($item['command'], $item['parameter'])
                );
            },
            $this->items
        );
        return $this->ui_factory->breadcrumbs($crumbs);
    }
}
