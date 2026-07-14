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

namespace ILIAS\Blog\Access;

class BlogAccess
{
    protected \ilWorkspaceTree $ws_tree;
    protected int $owner;
    protected int $id_type;
    protected \ilWorkspaceAccessHandler|\ilAccessHandler $access;
    protected ?int $node_id;
    protected int $user_id;

    public function __construct(
        $access_handler,
        ?int $node_id,
        int $id_type,
        int $user_id,
        int $owner
    ) {
        $this->access = $access_handler;
        $this->node_id = $node_id;
        $this->id_type = $id_type;
        $this->user_id = $user_id;
        $this->owner = $owner;
        $this->ws_tree = new \ilWorkspaceTree($this->owner);
    }

    public function canWrite(): bool
    {
        return $this->access->checkAccess('write', '', $this->node_id);
    }

    public function mayContribute(): bool
    {
        if ($this->id_type === \ilObject2GUI::WORKSPACE_NODE_ID) {
            return $this->checkPermissionBool("write");
        }

        return ($this->checkPermissionBool("redact") ||
            $this->checkPermissionBool("contribute"));
    }

    public function mayEditPosting(
        int $a_posting_id,
        int $a_author_id = null
    ): bool {
        // single author blog (owner) in personal workspace
        if ($this->id_type === \ilObject2GUI::WORKSPACE_NODE_ID) {
            return $this->checkPermissionBool("write");
        }

        // repository blogs

        // redact allows to edit all postings
        if ($this->checkPermissionBool("redact")) {
            return true;
        }

        // contribute gives access to own postings
        if ($this->checkPermissionBool("contribute")) {
            // check owner of posting
            if (!$a_author_id) {
                $post = new \ilBlogPosting($a_posting_id);
                $a_author_id = $post->getAuthor();
            }
            return $this->user_id === $a_author_id;
        }
        return false;
    }

    protected function checkPermissionBool(string $perm): bool
    {
        if (!is_null($this->node_id)) {
            return $this->access->checkAccess($perm, "", $this->node_id);
        }
        if ($this->owner === $this->user_id) {
            return true;
        }
        return false;
    }

    public function canReadPosting(int $posting_id): bool
    {
        return (
            $this->postingIdMatches($posting_id) &&
            $this->checkPermissionBool("read") &&
            ($this->mayContribute() ||
            \ilBlogPosting::_lookupActive($posting_id, "blp")));
    }

    public function canApprove(int $posting_id): bool
    {
        if (!$this->postingIdMatches($posting_id)) {
            return false;
        }
        return ($this->checkPermissionBool("redact") ||
            $this->checkPermissionBool("write")
        );
    }

    public function isActive(int $posting_id): bool
    {
        return (\ilBlogPosting::_lookupActive($posting_id, "blp"));
    }

    protected function postingIdMatches(int $posting_id): bool
    {
        if ($this->id_type === \ilObject2GUI::WORKSPACE_NODE_ID) {
            $obj_id = $this->ws_tree->lookupObjectId($this->node_id);
        } else {
            $obj_id = \ilObject::_lookupObjId($this->node_id);
        }
        return \ilBlogPosting::lookupBlogId($posting_id) === $obj_id;
    }

    public function getAccessHandler(): \ilAccessHandler|\ilWorkspaceAccessHandler
    {
        return $this->access;
    }

}
