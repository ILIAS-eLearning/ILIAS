<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\PageMetrics\Command;

/**
 * Class GetPageMetricsCommand
 * @package ILIAS\ContentPage\PageMetrics\Command
 * @author Michale Jansen <mjansen@databay.de>
 */
final class GetPageMetricsCommand
{
    private int $contentPageId;
    private string $language;

    public function __construct(int $contentPageId, string $language)
    {
        $this->contentPageId = $contentPageId;
        $this->language = $language;
    }

    public function getContentPageId() : int
    {
        return $this->contentPageId;
    }

    public function getLanguage() : string
    {
        return $this->language;
    }
}
