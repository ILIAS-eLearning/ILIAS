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

namespace ILIAS\MetaData\Editor\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\Tags\Tag as BaseTag;
use ILIAS\MetaData\Paths\PathInterface;

class Tag extends BaseTag implements TagInterface
{
    protected ?PathInterface $representation;
    protected ?PathInterface $preview;
    protected ?PathInterface $created_with;
    protected bool $is_collected;
    protected bool $last_in_tree;
    protected bool $important_label;

    public function __construct(
        ?PathInterface $preview,
        ?PathInterface $representation,
        ?PathInterface $created_with,
        bool $is_collected,
        bool $last_in_tree,
        bool $important_label
    ) {
        $this->preview = $preview;
        $this->representation = $representation;
        $this->created_with = $created_with;
        $this->is_collected = $is_collected;
        $this->last_in_tree = $last_in_tree;
        $this->important_label = $important_label;
    }

    public function hasRepresentation(): bool
    {
        return isset($this->representation);
    }

    public function representation(): ?PathInterface
    {
        return $this->representation;
    }

    public function hasPreview(): bool
    {
        return isset($this->preview);
    }

    public function preview(): ?PathInterface
    {
        return $this->preview;
    }

    public function isCreatedWithAnotherElement(): bool
    {
        return isset($this->created_with);
    }

    public function createdWith(): ?PathInterface
    {
        return $this->created_with;
    }

    public function isCollected(): bool
    {
        return $this->is_collected;
    }

    public function isLastInTree(): bool
    {
        return $this->last_in_tree;
    }

    public function isLabelImportant(): bool
    {
        return $this->important_label;
    }
}
