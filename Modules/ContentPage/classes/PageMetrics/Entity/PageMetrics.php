<?php

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

declare(strict_types=1);

namespace ILIAS\ContentPage\PageMetrics\Entity;

use ILIAS\ContentPage\PageMetrics\ValueObject\PageReadingTime;

/**
 * Class PageMetrics
 * @package ILIAS\ContentPage\PageMetrics\Entity
 */
class PageMetrics
{
    public function __construct(
        private readonly int $contentPageId,
        private readonly int $pageId,
        private readonly string $language,
        private readonly PageReadingTime $readingTime
    ) {
    }

    public function contentPageId(): int
    {
        return $this->contentPageId;
    }

    public function pageId(): int
    {
        return $this->pageId;
    }

    public function language(): string
    {
        return $this->language;
    }

    public function readingTime(): PageReadingTime
    {
        return $this->readingTime;
    }
}
