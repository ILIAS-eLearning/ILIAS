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

namespace ILIAS\UI\Implementation\Crawler\Exception;

class Factory
{
    /**
     * Those assertions are used to wrap the throwing of exception to make to code more readable.
     * @return CrawlerAssertion
     */
    public function assertion(): CrawlerAssertion
    {
        return new CrawlerAssertion();
    }

    /**
     * Crawler exceptions for each type of problem that can occur while parsing the entries.
     * @param int $type
     * @param string $info
     * @return CrawlerException
     */
    public function exception(int $type = -1, string $info = ""): CrawlerException
    {
        return new CrawlerException($type, $info);
    }
}
