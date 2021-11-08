<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler\Exception;

class Factory
{
    /**
     * Those assertions are used to wrap the throwing of exception to make to code more readable.
     * @return CrawlerAssertion
     */
    public function assertion() : CrawlerAssertion
    {
        return new CrawlerAssertion();
    }

    /**
     * Crawler exceptions for each type of problem that can occur while parsing the entries.
     * @param int $type
     * @param string $info
     * @return CrawlerException
     */
    public function exception(int $type = -1, string $info = "") : CrawlerException
    {
        return new CrawlerException($type, $info);
    }
}
