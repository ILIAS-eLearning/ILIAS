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

namespace ILIAS\News;

use ilLanguage;
use ilSetting;
use ilNewsItem;

class NewsPeriodInfo
{
    public function __construct(
        private readonly ilLanguage $lng,
        private readonly ilSetting $news_settings,
    ) {
    }

    public function getPeriodInfo(): string
    {
        return implode('<br />', $this->getPeriodInfoLines());
    }

    /**
     * @return list<string>
     */
    private function getPeriodInfoLines(): array
    {
        $period_info = $this->lng->txt('news_old_period');

        return [
            sprintf(
                $period_info,
                $this->lng->txt('news_pd_period'),
                $this->getDashboardNewsPeriodLabel()
            ),
            sprintf(
                $period_info,
                $this->lng->txt('news_co_period'),
                $this->getContainerObjectNewsPeriodLabel()
            ),
        ];
    }

    private function getDashboardNewsPeriodLabel(): string
    {
        return $this->formatPeriodLabel(ilNewsItem::_lookupDefaultPDPeriod());
    }

    private function getContainerObjectNewsPeriodLabel(): string
    {
        $default_co_period = ilNewsItem::_lookupDefaultCOPeriod();

        return $this->formatPeriodLabel(
            (int) ($this->news_settings->get('news_co_period', (string) $default_co_period) ?? $default_co_period)
        );
    }

    private function formatPeriodLabel(int $days): string
    {
        return match ($days) {
            -1 => $this->lng->txt('no_limit'),
            7 => "1 {$this->lng->txt('week')}",
            30 => "1 {$this->lng->txt('month')}",
            365, 366 => "1 {$this->lng->txt('year')}",
            default => sprintf($this->lng->txt('news_period_x_days'), $days),
        };
    }
}
