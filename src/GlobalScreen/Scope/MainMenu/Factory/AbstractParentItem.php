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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Class AbstractParentItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractParentItem extends AbstractBaseItem implements isParent
{
    /**
     * @var isItem[]
     */
    protected $children = [];

    /**
     * @inheritDoc
     */
    public function getChildren() : array
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     */
    public function withChildren(array $children) : isParent
    {
        $clone = clone($this);
        $clone->children = $children;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function appendChild(isItem $child) : isParent
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasChildren() : bool
    {
        return (count($this->children) > 0);
    }

    /**
     * @inheritDoc
     */
    public function removeChild(isItem $child_to_remove) : isParent
    {
        $this->children = array_filter($this->children, static function (isItem $item) use ($child_to_remove) : bool {
            return $item !== $child_to_remove;
        });

        return $this;
    }
}
