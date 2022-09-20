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

namespace ILIAS\ContentPage\PageMetrics;

use ILIAS\ContentPage\PageMetrics\Entity\PageMetrics;

/**
 * Interface PageMetricsRepository
 * @package ILIAS\ContentPage\PageMetrics
 * @author Michael Jansen <mjansen@databay.de>
 */
interface PageMetricsRepository
{
    public function store(PageMetrics $pageMetrics): void;

    public function delete(PageMetrics $pageMetrics): void;

    /**
     * @param int    $contentPageId
     * @param int    $pageId
     * @param string $language
     * @return PageMetrics
     * @throws CouldNotFindPageMetrics
     */
    public function findBy(int $contentPageId, int $pageId, string $language): PageMetrics;
}
