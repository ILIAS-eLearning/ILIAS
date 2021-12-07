<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics\Entity;

use ILIAS\ContentPage\PageMetrics\ValueObject\PageReadingTime;

/**
 * Class PageMetrics
 * @package ILIAS\ContentPage\PageMetrics\Entity
 */
class PageMetrics
{
    private int $contentPageId;
    private int $pageId;
    private string $language;
    private PageReadingTime $readingTime;

    public function __construct(int $contentPageId, int $pageId, string $language, PageReadingTime $readingTime)
    {
        $this->contentPageId = $contentPageId;
        $this->pageId = $pageId;
        $this->language = $language;
        $this->readingTime = $readingTime;
    }

    public function contentPageId() : int
    {
        return $this->contentPageId;
    }

    public function pageId() : int
    {
        return $this->pageId;
    }

    public function language() : string
    {
        return $this->language;
    }

    public function readingTime() : PageReadingTime
    {
        return $this->readingTime;
    }
}
