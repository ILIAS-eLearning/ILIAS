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

namespace ILIAS\Forum\Files\Access;

use ilDBConstants;
use ilDBInterface;

final readonly class DbOwnershipRepository implements OwnershipRepository
{
    public function __construct(private ilDBInterface $db)
    {
    }

    public function findPostingOwnership(int $posting_id): ?AttachmentOwnership
    {
        if ($posting_id <= 0) {
            return null;
        }

        $res = $this->db->queryF(
            'SELECT posts.pos_pk, posts.pos_top_fk, posts.pos_thr_fk, posts.pos_author_id, forum.top_frm_fk'
            . ' FROM frm_posts posts'
            . ' INNER JOIN frm_data forum ON forum.top_pk = posts.pos_top_fk'
            . ' WHERE posts.pos_pk = %s',
            [ilDBConstants::T_INTEGER],
            [$posting_id]
        );

        $row = $this->db->fetchAssoc($res);
        if (!\is_array($row)) {
            return null;
        }

        return new AttachmentOwnership(
            (int) $row['pos_pk'],
            (int) $row['pos_top_fk'],
            (int) $row['top_frm_fk'],
            (int) $row['pos_thr_fk'],
            (int) $row['pos_author_id']
        );
    }

    public function findDraftOwnership(int $draft_id): ?AttachmentOwnership
    {
        if ($draft_id <= 0) {
            return null;
        }

        $res = $this->db->queryF(
            'SELECT drafts.draft_id, drafts.forum_id, drafts.thread_id, drafts.post_author_id, forum.top_frm_fk'
            . ' FROM frm_posts_drafts drafts'
            . ' INNER JOIN frm_data forum ON forum.top_pk = drafts.forum_id'
            . ' WHERE drafts.draft_id = %s',
            [ilDBConstants::T_INTEGER],
            [$draft_id]
        );

        $row = $this->db->fetchAssoc($res);
        if (!\is_array($row)) {
            return null;
        }

        return new AttachmentOwnership(
            (int) $row['draft_id'],
            (int) $row['forum_id'],
            (int) $row['top_frm_fk'],
            (int) $row['thread_id'],
            (int) $row['post_author_id']
        );
    }
}
