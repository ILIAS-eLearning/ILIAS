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

namespace ILIAS\News\Data;

/**
 * News Context DTO represents a context where news items can be associated with. It encapsulates
 * all relevant information about the context and provides validation, caching, and serialization
 * capabilities.
 */
final class NewsContext
{
    public function __construct(
        private readonly int $ref_id,
        private readonly ?int $obj_id = null,
        private readonly ?string $obj_type = null,
        private readonly ?int $parent_ref_id = null,
        private readonly int $level = 0,
    ) {
    }

    /*
        Getters & Setters
     */

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getObjId(): ?int
    {
        return $this->obj_id;
    }

    public function getObjType(): ?string
    {
        return $this->obj_type;
    }

    public function getParentRefId(): ?int
    {
        return $this->parent_ref_id;
    }

    /**
     * @return int The level of this context in the hierarchy of contexts. This is different
     * from the depth in the ilias tree because it will be considered relative.
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /*
        Accessor Methods
     */

    /**
     * Check if this context is a child of another context
     */
    public function isChildOf(NewsContext $parent_context): bool
    {
        return $this->parent_ref_id === $parent_context->getRefId();
    }

    /**
     * Check if this context is a parent of another context
     */
    public function isParentOf(NewsContext $child_context): bool
    {
        return $child_context->getParentRefId() === $this->ref_id;
    }

    /**
     * Check if this context is at the root level
     */
    public function isRoot(): bool
    {
        return $this->parent_ref_id === null;
    }
}
