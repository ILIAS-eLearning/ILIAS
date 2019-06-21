<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

/**
 * Interface hasTitle
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasTitle extends isItem
{

    /**
     * @param string $title
     *
     * @return isTopItem
     */
    public function withTitle(string $title) : hasTitle;


    /**
     * @return string
     */
    public function getTitle() : string;
}
