<?php declare(strict_types=1);

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
