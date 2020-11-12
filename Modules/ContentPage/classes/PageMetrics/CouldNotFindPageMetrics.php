<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics;

use ilException;

/**
 * Class CouldNotFindPageMetrics
 * @package ILIAS\ContentPage\PageMetrics
 * @author Michael Jansen <mjansen@databay.de>
 */
final class CouldNotFindPageMetrics extends ilException
{
    /**
     * @param int    $contentPageId
     * @param int    $pageId
     * @param string $language
     * @return static
     */
    public static function by(int $contentPageId, int $pageId, string $language) : self
    {
        return new self(sprintf(
            "Could not find content page page metrics for page with page id %s and language %s for parent with id %s",
            $contentPageId,
            $pageId,
            $language
        ));
    }
}
