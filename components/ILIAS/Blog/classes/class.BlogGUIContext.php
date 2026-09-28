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

namespace ILIAS\Blog;

use ILIAS\Blog\Permission\PermissionManager;

class BlogGUIContext
{
    public function __construct(
        protected int $node_id,
        protected int $id_type,
        protected ?\ilObjBlog $blog,
        protected string $month,
        protected ?int $author,
        protected PermissionManager $permission,
        protected StandardGUIRequest $request,
        protected bool $call_by_reference = false
    ) {
    }

    public function getNodeId(): int
    {
        return $this->node_id;
    }

    public function getIdType(): int
    {
        return $this->id_type;
    }

    public function getObject(): ?\ilObjBlog
    {
        return $this->blog;
    }

    public function getBlog(): \ilObjBlog
    {
        if ($this->blog === null) {
            throw new \LogicException("A Blog GUI context requires a blog object.");
        }

        return $this->blog;
    }

    public function getMonth(): string
    {
        return $this->month;
    }

    public function getAuthor(): ?int
    {
        return $this->author;
    }

    public function getPermission(): PermissionManager
    {
        return $this->permission;
    }

    public function getRequest(): StandardGUIRequest
    {
        return $this->request;
    }

    public function isRepositoryNode(): bool
    {
        return $this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID;
    }

    public function isCallByReference(): bool
    {
        return $this->call_by_reference;
    }
}
