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

namespace ILIAS\ContentPage\PageMetrics\Command;

/**
 * Class StorePageMetricsCommand
 * @package ILIAS\ContentPage\PageMetrics\Command
 * @author Michale Jansen <mjansen@databay.de>
 */
final class StorePageMetricsCommand
{
    public function __construct(private readonly int $contentPageId, private readonly string $language)
    {
    }

    public function getContentPageId(): int
    {
        return $this->contentPageId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
