<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler\Entry;

use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Abstract Entry Part to share some common entry functionality
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class AbstractEntryPart
{
    /**
     * @var Crawler\Exception\Factory
     */
    protected $f = null;

    /**
     * AbstractEntryPart constructor.
     */
    protected function __construct()
    {
        $this->f = new Crawler\Exception\Factory();
    }

    /**
     * @return Crawler\Exception\CrawlerAssertion
     */
    protected function assert()
    {
        return $this->f->assertion();
    }
}
