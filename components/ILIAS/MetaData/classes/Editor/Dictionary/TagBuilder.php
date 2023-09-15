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

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

class TagBuilder
{
    protected PathFactoryInterface $path_factory;
    protected StructureElementInterface $element;

    protected ?PathInterface $representation = null;
    protected ?PathInterface $preview = null;
    protected ?PathInterface $created_with = null;
    protected bool $is_collected = false;
    protected bool $last_in_tree = false;
    protected bool $important_label = false;

    public function __construct(
        PathFactoryInterface $path_factory,
        StructureElementInterface $element
    ) {
        $this->path_factory = $path_factory;
        $this->element = $element;
    }

    public function withRepresentation(
        StructureElementInterface $element
    ): TagBuilder {
        $clone = clone $this;
        $clone->representation = $this->getPath($element);
        return $clone;
    }

    public function withPreview(
        StructureElementInterface $element
    ): TagBuilder {
        $clone = clone $this;
        $clone->preview = $this->getPath($element);
        return $clone;
    }

    public function withCreatedWith(
        StructureElementInterface $element
    ): TagBuilder {
        $clone = clone $this;
        $clone->created_with = $this->getPath($element);
        return $clone;
    }

    public function withIsCollected(bool $is_collected): TagBuilder
    {
        $clone = clone $this;
        $clone->is_collected = $is_collected;
        return $clone;
    }

    public function withLastInTree(bool $last_in_tree): TagBuilder
    {
        $clone = clone $this;
        $clone->last_in_tree = $last_in_tree;
        return $clone;
    }

    public function withImportantLabel(bool $important_label): TagBuilder
    {
        $clone = clone $this;
        $clone->important_label = $important_label;
        return $clone;
    }

    public function get(): TagInterface
    {
        return new Tag(
            $this->preview,
            $this->representation,
            $this->created_with,
            $this->is_collected,
            $this->last_in_tree,
            $this->important_label
        );
    }

    protected function getPath(
        StructureElementInterface $element
    ): PathInterface {
        return $this->path_factory->betweenElements($this->element, $element);
    }
}
