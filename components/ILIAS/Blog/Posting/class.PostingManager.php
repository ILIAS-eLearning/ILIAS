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

namespace ILIAS\Blog\Posting;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalRepoService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\News\NewsManager;

class PostingManager
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }

    public function create(Posting $posting): int
    {
        return $this->repo->posting()->create($posting);
    }

    public function update(Posting $posting): void
    {
        $this->repo->posting()->update($posting);
    }

    public function delete(int $posting_id): void
    {
        $this->repo->posting()->delete($posting_id);
    }

    public function getById(int $id): ?Posting
    {
        return $this->repo->posting()->getById($id);
    }

    /**
     * @return Posting[]
     */
    public function getAllByBlog(int $blog_id, int $limit = 1000, int $offset = 0): array
    {
        return $this->repo->posting()->getAllByBlog($blog_id, $limit, $offset);
    }

    public function exists(int $blog_id, int $posting_id): bool
    {
        return $this->repo->posting()->exists($blog_id, $posting_id);
    }

    public function lookupBlogId(int $posting_id): ?int
    {
        return $this->repo->posting()->lookupBlogId($posting_id);
    }

    public function deleteAllByBlog(int $blog_id): void
    {
        $this->repo->posting()->deleteAllBlogPostings($blog_id);
    }

    public function getLastPost(int $blog_id): int
    {
        return $this->repo->posting()->getLastPost($blog_id);
    }

    public function searchBlogsByAuthor(int $user_id): array
    {
        return $this->repo->posting()->searchBlogsByAuthor($user_id);
    }
}
