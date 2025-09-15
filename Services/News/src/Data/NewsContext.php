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

use DateTimeImmutable;

/**
 * News Context DTO represents a context where news items can be associated with. It encapsulates
 * all relevant information about the context and provides validation, caching, and serialization
 * capabilities.
 */
final class NewsContext
{
    public function __construct(
        private int $ref_id,
        private ?int $obj_id = null,
        private ?string $obj_type = null,
        private ?int $parent_ref_id = null,
        private ?int $sub_obj_id = null,
        private ?string $sub_obj_type = null,
        private int $level = 0,
        private ?DateTimeImmutable $last_news_date = null,
        private int $news_count = 0,
        private bool $has_news = false,
        private array $child_contexts = [],
        private array $metadata = []
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

    public function getSubObjId(): ?int
    {
        return $this->sub_obj_id;
    }

    public function getSubObjType(): ?string
    {
        return $this->sub_obj_type;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getLastNewsDate(): ?DateTimeImmutable
    {
        return $this->last_news_date;
    }

    public function getNewsCount(): int
    {
        return $this->news_count;
    }

    public function hasNews(): bool
    {
        return $this->has_news;
    }

    public function getChildContexts(): array
    {
        return $this->child_contexts;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function withChildContext(NewsContext $child_context): self
    {
        $new = clone $this;
        $new->child_contexts[] = $child_context;
        return $new;
    }

    public function withMetadata(string $key, mixed $value): self
    {
        $new = clone $this;
        $new->metadata[$key] = $value;
        return $new;
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

    /**
     * Check if this context has sub-objects
     */
    public function hasSubObject(): bool
    {
        return $this->sub_obj_id !== null && $this->sub_obj_type !== null;
    }

    /**
     * Check if this context supports aggregation (courses, groups, or categories)
     */
    public function supportsAggregation(): bool
    {
        return in_array($this->obj_type, ['crs', 'grp', 'cat'], true);
    }

    /**
     * Check if this context supports nesting (courses, groups, or categories)
     */
    public function supportsNesting(): bool
    {
        return in_array($this->obj_type, ['crs', 'grp', 'cat'], true);
    }
}
