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

    public function minutes(): int
    {
        return $this->minutes;
    }
}
