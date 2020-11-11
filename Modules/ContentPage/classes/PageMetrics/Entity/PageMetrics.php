<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics\Entity;

use ILIAS\ContentPage\PageMetrics\ValueObject\PageReadingTime;

/**
 * Class PageMetrics
 * @package ILIAS\ContentPage\PageMetrics\Entity
 */
final class PageMetrics
{
    /** @var int */
    private $contentPageId;
    /** @var int */
    private $pageId;
    /** @var string */
    private $language;
    /** @var PageReadingTime */
    private $readingTime;

    /**
     * PageMetrics constructor.
     * @param int             $contentPageId
     * @param int             $pageId
     * @param string          $language
     * @param PageReadingTime $readingTime
     */
    public function __construct(int $contentPageId, int $pageId, string $language, PageReadingTime $readingTime)
    {
        $this->contentPageId = $contentPageId;
        $this->pageId = $pageId;
        $this->language = $language;
        $this->readingTime = $readingTime;
    }

    /**
     * @return int
     */
    public function contentPageId() : int
    {
        return $this->contentPageId;
    }

    /**
     * @return int
     */
    public function pageId() : int
    {
        return $this->pageId;
    }

    /**
     * @return string
     */
    public function language() : string
    {
        return $this->language;
    }

    /**
     * @return PageReadingTime
     */
    public function readingTime() : PageReadingTime
    {
        return $this->readingTime;
    }
}
