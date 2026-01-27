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

namespace ILIAS\News\Timeline;

use ILIAS\News\Data\NewsCollection;
use ILIAS\News\Data\NewsContext;
use ILIAS\News\Data\NewsCriteria;
use ILIAS\News\Data\NewsItem;
use ILIAS\News\InternalRepoService;
use ILIAS\News\InternalDataService;
use ILIAS\News\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class TimelineManager
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }

    public function getNewsData(
        int $ref_id,
        int $context_obj_id,
        string $context_type,
        int $period,
        bool $include_auto_entries,
        int $items_per_load,
        array $excluded
    ): NewsCollection {
        $criteria = new NewsCriteria(
            period: $period,
            limit: $items_per_load,
            no_auto_generated: !$include_auto_entries,
            excluded_news_ids: $excluded,
            read_user_id: $this->domain->user()->getId()
        );

        if ($ref_id > 0) {
            return $this->domain->collection()->getNewsForContext(
                new NewsContext($ref_id, $context_obj_id, $context_type),
                $criteria,
                $this->domain->user()->getId()
            );
        } else {
            return $this->domain->collection()->getNewsForUser(
                $this->domain->user(),
                $criteria
            );
        }
    }

    public function getNewsItem(int $news_id): ?NewsItem
    {
        return $this->repo->news()->findById($news_id);
    }
}
