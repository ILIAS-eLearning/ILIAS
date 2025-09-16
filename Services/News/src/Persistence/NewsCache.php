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

namespace ILIAS\News\Persistence;

use ILIAS\News\Data\NewsCollection;
use ILIAS\News\Data\NewsContext;
use ILIAS\News\Data\NewsCriteria;

/**
 * Multi-Level News Cache Implementation:
 *
 * - Level 1: Context Cache - Context-specific data
 * - Level 2: User Cache - User-specific data
 * - Level 3: Hot Cache - Resolved and filtered user-specific data
 */
class NewsCache
{
    protected readonly bool $enabled;
    protected readonly \ilCache $il_cache;

    public function __construct(
    ) {
        $settings = new \ilSetting('news');
        $this->enabled = $settings->get('acc_cache_mins') !== 0;

        $this->il_cache = new \ilCache('ServicesNews', 'NewsMuliLevel');
        $this->il_cache->setExpiresAfter($settings->get('acc_cache_mins') * 60);
    }

    /**
     * Level-1 Cache stores a collection of the news items for the provided context (ref_id). It returns a list of the
     * NewsItem-IDs or null on cache miss.
     *
     * @return int[]|null
     */
    public function getNewsForContext(NewsContext $context, NewsCriteria $criteria): ?array
    {
        //TODO: implement
        return null;
    }

    public function storeNewsForContext(NewsContext $context, NewsCriteria $criteria, NewsCollection $news): void
    {
        //TODO: implement
    }

    public function invalidateNewsForContext(NewsContext $context, NewsCriteria $criteria): void
    {
        //TODO: implement
    }

    /**
     * Level-2 Cache stores a collection of the news contexts for a specific user. It returns a list of the
     * NewsContexts (ref_id only) or null on cache miss.
     *
     * @return NewsContext|null
     */
    public function getUserContextAccess(int $user_id, NewsCriteria $criteria): ?array
    {
        //TODO: implement
        return [];
    }

    public function storeUserContextAccess(int $user_id, NewsCriteria $criteria, array $contexts): void
    {
        //TODO: implement
    }

    public function invalidateUserContextAccess(int $user_id, NewsCriteria $criteria): void
    {
        //TODO: implement
    }

    /**
     * Level-3 Cache stores a collection of the news items for a specific user. It returns a NewsCollection or null on
     * cache miss.
     *
     * @return NewsContext|null
     */
    public function getNewsForUser(int $user_id, NewsCriteria $criteria): ?NewsCollection
    {
        //TODO: implement
        return null;
    }

    public function storeNewsForUser(int $user_id, array $contexts, NewsCriteria $criteria, NewsCollection $news): void
    {
        //TODO: implement
    }

    public function invalidateNewsForUser(int $user_id, array $contexts, NewsCriteria $criteria): void
    {
        //TODO: implement
    }
}
