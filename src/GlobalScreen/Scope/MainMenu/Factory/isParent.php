<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface isParent
 *
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
     *
     * @return isParent
     */
    public function withChildren(array $children) : isParent;


    /**
     * Attention
     *
     * @param isChild $child
     *
     * @return isParent
     */
    public function appendChild(isChild $child) : isParent;


    /**
     * @return bool
     */
    public function hasChildren() : bool;
}
