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

namespace ILIAS\News\Dashboard;

use ILIAS\News\Data\NewsCriteria;
use ILIAS\News\InternalRepoService;
use ILIAS\News\InternalDataService;
use ILIAS\News\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DashboardNewsManager
{
    protected DashboardSessionRepository $session_repo;
    protected \ilFavouritesManager $fav_manager;

    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
        $this->session_repo = $repo->dashboard();
        $this->fav_manager = new \ilFavouritesManager();
    }

    public function getDashboardNewsPeriod(): int
    {
        $user = $this->domain->user();
        $period = $this->session_repo->getDashboardNewsPeriod();
        if ($period === 0) {
            $period = \ilNewsItem::_lookupUserPDPeriod($user->getId());
        }
        return $period;
    }

    public function getDashboardSelectedRefId(): int
    {
        $user = $this->domain->user();
        return (int) $user->getPref("news_sel_ref_id");
    }

    public function saveFilterData(?array $data): void
    {
        $user = $this->domain->user();
        if (!is_null($data) && !is_null($data["news_ref_id"])) {
            $user->writePref("news_sel_ref_id", (string) (int) $data["news_ref_id"]);
        } else {
            $user->writePref("news_sel_ref_id", "0");
        }
        $this->session_repo->setDashboardNewsPeriod((int) ($data["news_per"] ?? 0));
    }

    /**
     * @return array<int,string>
     */
    public function getPeriodOptions(): array
    {
        $lng = $this->domain->lng();
        $news_set = new \ilSetting("news");
        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");
        $default_per = \ilNewsItem::_lookupDefaultPDPeriod();

        $options = [
            "7" => $lng->txt("news_period_1_week"),
            "30" => $lng->txt("news_period_1_month"),
            "366" => $lng->txt("news_period_1_year")
        ];

        return $options;

        /*
        $unset = [];
        foreach ($options as $k => $opt) {
            if (!$allow_shorter_periods && ($k < $default_per)) {
                $unset[$k] = $k;
            }
            if (!$allow_longer_periods && ($k > $default_per)) {
                $unset[$k] = $k;
            }
        }
        foreach ($unset as $k) {
            unset($options[$k]);
        }

        return $options;*/
    }

    /**
     * @return array<int,string>
     */
    public function getContextOptions(): array
    {
        $context_count = $this->domain->collection()->countNewsByContext(
            $this->domain->user(),
            new NewsCriteria(period: $this->getDashboardNewsPeriod(), only_public: false)
        );

        $options = [];
        foreach ($context_count as [$context, $count]) {
            $options[$context->getRefId()] = \ilObject::_lookupTitle($context->getObjId()) . " ({$count})";
        }
        asort($options);

        return [0 => $this->domain->lng()->txt('news_all_items')] + $options;
    }
}
