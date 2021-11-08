<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler\Entry;

use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Abstract Entry Part to share some common entry functionality
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class AbstractEntryPart
{
    protected ?Crawler\Exception\Factory $f = null;

    public function __construct()
    {
        $this->f = new Crawler\Exception\Factory();
    }

    protected function assert() : Crawler\Exception\CrawlerAssertion
    {
        return $this->f->assertion();
    }
}
