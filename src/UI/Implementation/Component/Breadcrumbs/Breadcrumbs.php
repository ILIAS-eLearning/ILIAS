<?php declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Breadcrumbs;

use ILIAS\UI\Component\Breadcrumbs as B;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Link\Standard;

class Breadcrumbs implements B\Breadcrumbs
{
    use ComponentHelper;

    /**
     * @var Standard[]     list of links
     */
    protected array $crumbs;

    /**
     * @param \ILIAS\UI\Component\Link\Standard[] $crumbs
     */
    public function __construct(array $crumbs)
    {
        $types = array(Standard::class);
        $this->checkArgListElements("crumbs", $crumbs, $types);
        $this->crumbs = $crumbs;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->crumbs;
    }

    /**
     * @inheritdoc
     */
    public function withAppendedItem(Standard $crumb) : B\Breadcrumbs
    {
        $clone = clone $this;
        $clone->crumbs[] = $crumb;
        return $clone;
    }
}
