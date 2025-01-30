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

namespace ILIAS\Forum\Thread;

use ForumDto;
use ilForum;
use ilForumAuthorInformationCache;
use ilForumProperties;
use ilForumTopic;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use ilObjForum;
use ilSession;
use ThreadSortation;

class ForumThreadTableSessionStorage
{
    public const string KEY_THREAD_SORTATION = 'thread_sortation';
    public const string KEY_THREAD_PAGE = 'thread_page';

    private WrapperFactory $http_wrapper;
    private Factory $refinery;

    public function __construct(
        private readonly int  $forum_ref_id,
        private readonly bool $is_moderator,
        ?WrapperFactory       $http_wrapper = null,
        ?Factory              $refinery = null
    ) {
        global $DIC;
        $this->http_wrapper = $http_wrapper ?? $DIC->http()->wrapper();
        $this->refinery = $refinery ?? $DIC->refinery();
    }

    public function fetchData(ilForum $forum, ForumDto $topicData): ThreadsPage
    {
        $sortation = $this->getThreadSortation();
        $page = $this->getThreadPage();
        $limit = ilForumProperties::PAGE_SIZE_THREAD_OVERVIEW;

        $params = [
            'is_moderator' => $this->is_moderator,
            'excluded_ids' => [],
            'order_column' => $sortation->field(),
            'order_direction' => $sortation->direction()
        ];

        $data = $forum->getAllThreads(
            $topicData->getTopPk(),
            $params,
            $limit,
            $page * $limit
        );
        if ($data['items'] === [] && $page > 0) {
            ilSession::set($this->buildSessionKey($this->forum_ref_id, self::KEY_THREAD_PAGE), 0);
            $data = $forum->getAllThreads(
                $topicData->getTopPk(),
                $params,
                $limit,
                $page * $limit
            );
        }

        $items = $data['items'];

        $thread_ids = [];
        $user_ids = [];
        foreach ($items as $thread) {
            /** @var ilForumTopic $thread */
            $thread_ids[] = $thread->getId();
            if ($thread->getDisplayUserId() > 0) {
                $user_ids[$thread->getDisplayUserId()] = $thread->getDisplayUserId();
            }
        }

        $user_ids = array_merge(
            ilObjForum::getUserIdsOfLastPostsByRefIdAndThreadIds($this->forum_ref_id, $thread_ids),
            $user_ids
        );

        ilForumAuthorInformationCache::preloadUserObjects(array_unique($user_ids));

        return new ThreadsPage($items);
    }

    public function getThreadSortation(): ThreadSortation
    {
        $query_thread_sortation = $this->getKeyValueFromQuery(self::KEY_THREAD_SORTATION);

        if ($query_thread_sortation !== null) {
            $this->setSessionKeyValue($this->forum_ref_id, self::KEY_THREAD_SORTATION, $query_thread_sortation);
            return ThreadSortation::tryFrom($query_thread_sortation);
        }

        $session_thread_sortation = $this->getKeyValueFromSession(
            $this->forum_ref_id,
            self::KEY_THREAD_SORTATION,
            ThreadSortation::DEFAULT_SORTATION->value
        );

        return ThreadSortation::tryFrom(
            $session_thread_sortation
        );
    }

    public function getThreadPage(): int
    {
        $query_thread_page = $this->getKeyValueFromQuery(self::KEY_THREAD_PAGE, null);

        if ($query_thread_page !== null) {
            $this->setSessionKeyValue($this->forum_ref_id, self::KEY_THREAD_PAGE, $query_thread_page);
            return $query_thread_page;
        }

        return $this->getKeyValueFromSession(
            $this->forum_ref_id,
            self::KEY_THREAD_PAGE,
            $query_thread_page
        );
    }

    private function getKeyValueFromQuery(string $key, ?int $default = null): ?int
    {
        return $this->http_wrapper->query()->retrieve(
            $key,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always($default)
            ])
        );
    }

    private function getKeyValueFromSession(int $refId, string $key, mixed $default = null): mixed
    {
        $session_value = ilSession::get($this->buildSessionKey($refId, $key));
        if ($session_value === null) {
            $session_value = $default;
        }

        return $session_value;
    }

    private function setSessionKeyValue(int $refId, string $key, mixed $value): void
    {
        ilSession::set($this->buildSessionKey($refId, $key), $value);
    }

    private function buildSessionKey(int $refId, string $key): string
    {
        return "frm_{$refId}_$key";
    }
}
