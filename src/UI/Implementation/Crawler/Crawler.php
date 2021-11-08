<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler;

use ILIAS\UI\Implementation\Crawler\Entry as Entry;

/**
 * Crawls all UI components for YAML information.
 */
interface Crawler
{
    /**
     * Starts with the factory indicated by factory path and crawles form this point all subsequent factories
     * recursively relying on the return statement given for each abstract component.
     */
    public function crawlFactory(
        string $factoryPath,
        Entry\ComponentEntry $parent = null,
        int $depth = 0
    ) : Entry\ComponentEntries;
}
