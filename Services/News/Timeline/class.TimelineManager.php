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

namespace ILIAS\News\Timeline;

use ILIAS\News\InternalRepoService;
use ILIAS\News\InternalDataService;
use ILIAS\News\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class TimelineManager
{
    protected InternalRepoService $repo;
    protected InternalDataService $data;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->data = $data;
        $this->domain = $domain;
    }

    public function getNewsData(
        int $ref_id,
        int $context_obj_id,
        string $context_type,
        int $period,
        bool $include_auto_entries,
        int $items_per_load,
        array $excluded
    ): array {
        $user = $this->domain->user();
        $news_item = new \ilNewsItem();
        $news_item->setContextObjId($context_obj_id);
        $news_item->setContextObjType($context_type);

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
                $items_per_load,
                $excluded
            );
        } else {
            $cnt = [];
            $news_data = \ilNewsItem::_getNewsItemsOfUser(
                $user->getId(),
                false,
                true,
                $period,
                $cnt,
                !$include_auto_entries,
                $excluded,
                $items_per_load
            );
        }
        return $news_data;
    }
}
