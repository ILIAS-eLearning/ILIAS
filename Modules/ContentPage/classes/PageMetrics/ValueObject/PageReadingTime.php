<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics\ValueObject;

use ilException;

/**
 * Class PageReadingTime
 * @package ILIAS\ContentPage\PageMetrics\ValueObject
 * @author Michael Jansen <mjansen@databay.de>
 */
class PageReadingTime
{
    private int $minutes;

    /**
     * PageReadingTime constructor.
     * @param int $minutes
     * @throws ilException
     */
    public function __construct(int $minutes)
    {
        if ($minutes < 0) {
            throw new ilException('The reading time MUST be a positive integer!');
        }

        if ($minutes > PHP_INT_MAX) {
            throw new ilException('The reading time MUST NOT exceed the maximum integer!');
        }

        $this->minutes = $minutes;
    }

    public function minutes() : int
    {
        return $this->minutes;
    }
}
