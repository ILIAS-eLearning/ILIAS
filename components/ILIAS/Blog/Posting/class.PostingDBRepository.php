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

use ilDBInterface;
use ilDateTime;
use ILIAS\Blog\InternalDataService;

class PostingDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    protected function getPostingFromRecord(array $rec): Posting
    {
        return $this->data->posting(
            (int) $rec['id'],
            (int) $rec['blog_id'],
            (string) $rec['title'],
            new ilDateTime($rec['created'], IL_CAL_DATETIME),
            (int) $rec['author'],
            (bool) $rec['approved'],
            $rec['last_withdrawn'] !== null
                ? new ilDateTime($rec['last_withdrawn'], IL_CAL_DATETIME)
                : null,
            \ilBlogPosting::_lookupActive((int) $rec['id'], "blp")
        );
    }

    public function create(Posting $posting): int
    {
        $id = $this->db->nextId('il_blog_posting');
        $this->db->insert('il_blog_posting', [
            'id' => ['integer', $id],
            'blog_id' => ['integer', $posting->getBlogId()],
            'title' => ['text', $posting->getTitle()],
            'created' => ['timestamp', $posting->getCreated()->get(IL_CAL_DATETIME)],
            'author' => ['integer', $posting->getAuthor()],
            'approved' => ['integer', $posting->isApproved()],
            'last_withdrawn' => ['timestamp', $posting->getLastWithdrawn()?->get(IL_CAL_DATETIME)],
        ]);
        return $id;
    }

    public function update(Posting $posting): void
    {
        $this->db->update('il_blog_posting', [
            'title' => ['text', $posting->getTitle()],
            'created' => ['timestamp', $posting->getCreated()->get(IL_CAL_DATETIME)],
            'approved' => ['integer', $posting->isApproved()],
            'last_withdrawn' => ['timestamp', $posting->getLastWithdrawn()?->get(IL_CAL_DATETIME)],
        ], [
            'id' => ['integer', $posting->getId()],
        ]);
    }

    public function getById(int $id): ?Posting
    {
        $set = $this->db->queryF(
            'SELECT * FROM il_blog_posting WHERE id = %s',
            ['integer'],
            [$id]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return $this->getPostingFromRecord($rec);
        }
        return null;
    }

    public function delete(int $id): void
    {
        $this->db->manipulateF(
            'DELETE FROM il_blog_posting WHERE id = %s',
            ['integer'],
            [$id]
        );
    }

    public function deleteAllBlogPostings(int $blog_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM il_blog_posting WHERE blog_id = %s',
            ['integer'],
            [$blog_id]
        );
    }

    public function lookupBlogId(int $posting_id): ?int
    {
        $set = $this->db->queryF(
            'SELECT blog_id FROM il_blog_posting WHERE id = %s',
            ['integer'],
            [$posting_id]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return (int) $rec['blog_id'];
        }
        return null;
    }

    public function getAllByBlog(int $blog_id, int $limit = 1000, int $offset = 0): array
    {
        if ($limit) {
            $this->db->setLimit($limit, $offset);
        }
        $set = $this->db->queryF(
            'SELECT * FROM il_blog_posting WHERE blog_id = %s ORDER BY created DESC',
            ['integer'],
            [$blog_id]
        );
        $posts = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $posts[] = $this->getPostingFromRecord($rec);
        }
        return $posts;
    }

    public function exists(int $blog_id, int $posting_id): bool
    {
        $set = $this->db->queryF(
            'SELECT id FROM il_blog_posting WHERE blog_id = %s AND id = %s',
            ['integer', 'integer'],
            [$blog_id, $posting_id]
        );
        return $this->db->numRows($set) > 0;
    }

    public function getLastPost(int $blog_id): int
    {
        $all = $this->getAllByBlog($blog_id, 1, 0);
        return $all ? $all[0]->getId() : 0;
    }

    public function searchBlogsByAuthor(int $user_id): array
    {
        $ids = [];
        $set = $this->db->queryF(
            'SELECT DISTINCT(blog_id) FROM il_blog_posting WHERE author = %s',
            ['integer'],
            [$user_id]
        );
        while ($row = $this->db->fetchAssoc($set)) {
            $ids[] = (int) $row['blog_id'];
        }
        return $ids;
    }
}
