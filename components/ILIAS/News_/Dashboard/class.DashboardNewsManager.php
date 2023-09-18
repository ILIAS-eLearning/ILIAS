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

namespace ILIAS\News\Dashboard;

use ILIAS\News\InternalRepoService;
use ILIAS\News\InternalDataService;
use ILIAS\News\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DashboardNewsManager
{
    protected DashboardSessionRepository $session_repo;
    protected InternalRepoService $repo;
    protected InternalDataService $data;
    protected InternalDomainService $domain;
    protected \ilFavouritesManager $fav_manager;
    /**
     * @var ?int[]
     */
    protected static ?array $user_object_ref_ids = null;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->session_repo = $repo->dashboard();
        $this->data = $data;
        $this->domain = $domain;
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
            7 => $lng->txt("news_period_1_week"),
            30 => $lng->txt("news_period_1_month"),
            366 => $lng->txt("news_period_1_year")
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
        $lng = $this->domain->lng();
        $user = $this->domain->user();
        $period = $this->getDashboardNewsPeriod();

        $cnt = [];
        \ilNewsItem::_getNewsItemsOfUser(
            $user->getId(),
            false,
            true,
            $period,
            $cnt
        );

        $ref_ids = $this->getUserNewsObjectRefIds();

        // related objects (contexts) of news
        $contexts[0] = $lng->txt("news_all_items");

        $conts = [];
        foreach ($ref_ids as $ref_id) {
            $obj_id = \ilObject::_lookupObjId($ref_id);
            $title = \ilObject::_lookupTitle($obj_id);
            $conts[$ref_id] = $title;
        }

        asort($conts);
        foreach ($conts as $ref_id => $title) {
            $contexts[$ref_id] = $title . " (" . (int) $cnt[$ref_id] . ")";
        }

        return $contexts;
    }

    /**
     * User news on the daashboard/news overview are presented for
     * all favourites and all memberships of the user.
     * @return int[]
     */
    protected function getUserNewsObjectRefIds(): array
    {
        if (is_null(self::$user_object_ref_ids)) {
            $ref_ids = [];
            $user = $this->domain->user();
            $user_id = $user->getId();

            // get all items of the personal desktop
            $fav_items = $this->fav_manager->getFavouritesOfUser($user_id);
            foreach ($fav_items as $item) {
                if (!in_array($item["ref_id"], $ref_ids)) {
                    $ref_ids[] = (int) $item["ref_id"];
                }
            }

            // get all memberships
            $crs_mbs = \ilParticipants::_getMembershipByType($user_id, ['crs']);
            $grp_mbs = \ilParticipants::_getMembershipByType($user_id, ['grp']);
            $items = array_merge($crs_mbs, $grp_mbs);
            foreach ($items as $i) {
                $item_references = \ilObject::_getAllReferences($i);
                $ref_ids = array_unique(array_merge($ref_ids, $item_references));
            }
            self::$user_object_ref_ids = $ref_ids;
        }
        return self::$user_object_ref_ids;
    }

    protected function getNewsForOverview(
        int $ref_id,
        int $period,
        bool $include_auto_entries,
        int $items_per_load
    ): array {
        $user = $this->domain->user();
        $news_item = new \ilNewsItem();
        //$news_item->setContextObjId($this->ctrl->getContextObjId());
        //$news_item->setContextObjType($this->ctrl->getContextObjType());

        if ($ref_id > 0) {
            $news_data = $news_item->getNewsForRefId(
                $ref_id,
                false,
                false,
                $period,
                true,
                false,
                !$include_auto_entries,
                false,
                null,
                $items_per_load
            );
        } else {
            $cnt = [];
            $news_data = \ilNewsItem::_getNewsItemsOfUser(
                $user->getId(),
                false,
                true,
                $period,
                $cnt,
                !$include_auto_entries
            );
        }
        return $news_data;
    }
}
