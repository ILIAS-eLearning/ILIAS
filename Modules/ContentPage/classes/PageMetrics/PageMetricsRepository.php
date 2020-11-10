<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics;

use ILIAS\ContentPage\PageMetrics\Entity\PageMetrics;

/**
 * Interface PageMetricsRepository
 * @package ILIAS\ContentPage\PageMetrics
 * @author Michael Jansen <mjansen@databay.de>
 */
interface PageMetricsRepository
{
    /**
     * @param PageMetrics $pageMetrics
     */
    public function store(PageMetrics $pageMetrics) : void;

    /**
     * @param PageMetrics $pageMetrics
     */
    public function delete(PageMetrics $pageMetrics) : void;

    /**
     * @param int    $contentPageId
     * @param int    $pageId
     * @param string $language
     * @return PageMetrics
     * @throws CouldNotFindPageMetrics
     */
    public function findBy(int $contentPageId, int $pageId, string $language) : PageMetrics;
}
