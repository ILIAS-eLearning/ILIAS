<?php

declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;

/**
 * GUI for Locator element
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

class ilLSLocatorGUI
{
    /**
     * @var UrlBuilder
     */
    protected $url_builder;

    /**
     * @var array
     */
    protected $items;

    public function __construct(
        LSUrlBuilder $url_builder,
        Factory $ui_factory
    ) {
        $this->url_builder = $url_builder;
        $this->ui_factory = $ui_factory;
    }

    public function withItems(array $items)
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
