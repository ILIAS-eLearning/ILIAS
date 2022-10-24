<?php

declare(strict_types=1);

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
    ): Entry\ComponentEntries;
}
