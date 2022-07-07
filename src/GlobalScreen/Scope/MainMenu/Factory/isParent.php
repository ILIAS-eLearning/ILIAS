<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface isParent
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isParent extends isItem
{

    /**
     * @return isItem[]
     */
    public function getChildren() : array;

    /**
     * @param isItem[] $children
     * @return isParent
     */
    public function withChildren(array $children) : isParent;

    /**
     * Attention
     *
     * @param isItem $child
     * @return isParent
     */
    public function appendChild(isItem $child) : isParent;

    /**
     * @param isItem $child_to_remove
     * @return isParent
     */
    public function removeChild(isItem $child_to_remove) : isParent;

    /**
     * @return bool
     */
    public function hasChildren() : bool;
}
