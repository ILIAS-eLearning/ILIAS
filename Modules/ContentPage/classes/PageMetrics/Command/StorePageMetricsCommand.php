<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics\Command;

/**
 * Class StorePageMetricsCommand
 * @package ILIAS\ContentPage\PageMetrics\Command
 * @author Michale Jansen <mjansen@databay.de>
 */
final class StorePageMetricsCommand
{
    /** @var int */
    private $contentPageId;
    /** @var string */
    private $language;

    /**
     * StorePageMetricsCommand constructor.
     * @param int    $contentPageId
     * @param string $language
     */
    public function __construct(int $contentPageId, string $language)
    {
        $this->contentPageId = $contentPageId;
        $this->language = $language;
    }

    /**
     * @return int
     */
    public function getContentPageId() : int
    {
        return $this->contentPageId;
    }

    /**
     * @return string
     */
    public function getLanguage() : string
    {
        return $this->language;
    }
}
