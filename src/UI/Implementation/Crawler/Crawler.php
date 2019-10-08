<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler;

use ILIAS\UI\Implementation\Crawler\Entry as Entry;

/**
 * Crawls all UI components for YAML information.
 */
interface Crawler
{
    /**
     * Starts with the factory indicated by factory path and crawles form this point all all subsequent factories
     * recursively relying on the return statement given for each abstract component.
     *
     * @param	string $factoryPath
     * @param	Entry\ComponentEntry|null $parent
     * @param	int $depth
     * @return	Entry\ComponentEntries
     */
    public function crawlFactory($factoryPath, Entry\ComponentEntry $parent = null, $depth=0);
}
