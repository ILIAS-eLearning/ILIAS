<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Breadcrumbs;

use ILIAS\UI\Component\Breadcrumbs as B;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Breadcrumbs implements B\Breadcrumbs
{
    use ComponentHelper;

    /**
     * @var Link\Standard[]     list of links
     */
    protected $crumbs;

    public function __construct(array $crumbs)
    {
        $types = array(\ILIAS\UI\Component\Link\Standard::class);
        $this->checkArgListElements("crumbs", $crumbs, $types);
        $this->crumbs = $crumbs;
    }


    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->crumbs;
    }

    /**
     * @inheritdoc
     */
    public function withAppendedItem($crumb)
    {
        $this->checkArgInstanceOf("crumb", $crumb, \ILIAS\UI\Component\Link\Standard::class);
        $clone = clone $this;
        $clone->crumbs[] = $crumb;
        return $clone;
    }
}
