<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component\Panel as P;
use ILIAS\UI\NotImplementedException;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Factory implements \ILIAS\UI\Component\Panel\Factory
{
    /**
     * @var Listing\Factory
     */
    protected $listing_factory;

    public function __construct(P\Listing\Factory $listing_factory)
    {
        $this->listing_factory = $listing_factory;
    }

    /**
     * @inheritdoc
     */
    public function standard($title, $content)
    {
        return new Standard($title, $content);
    }

    /**
     * @inheritdoc
     */
    public function sub($title, $content)
    {
        return new Sub($title, $content);
    }

    /**
     * @inheritdoc
     */
    public function report($title, $sub_panels)
    {
        return new Report($title, $sub_panels);
    }

    /**
     * @inheritdoc
     */
    public function listing()
    {
        return $this->listing_factory;
    }
}
