<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Link;
use ILIAS\UI\NotImplementedException;

/**
 * Footer
 */
class Footer implements MainControls\Footer
{
    use ComponentHelper;

    private $text = '';

    private $links = [];

    private $modals = [];

    /**
     * @var string
     */
    protected $permanent_url = "";

    public function __construct(array $links, string $text = '')
    {
        $types = [\ILIAS\UI\Component\Link\Link::class, \ILIAS\UI\Component\Button\Shy::class,];
        $this->checkArgListElements('links', $links, $types);
        $this->links = $links;
        $this->text = $text;
    }

    public function getLinks() : array
    {
        return $this->links;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function withPermanentURL(\ILIAS\Data\URI $url) : MainControls\Footer
    {
        $clone = clone $this;
        $clone->permanent_url = $url;
        return $clone;
    }

    public function getPermanentURL()
    {
        return $this->permanent_url;
    }

    public function getModals() : array
    {
        return $this->modals;
    }

    public function withModals(array $modals) : MainControls\Footer
    {
        $types = [\ILIAS\UI\Component\Modal\Modal::class,];
        $this->checkArgListElements('modals', $modals, $types);

        $clone = clone $this;
        $clone->modals = $modals;
        return $clone;
    }
}
