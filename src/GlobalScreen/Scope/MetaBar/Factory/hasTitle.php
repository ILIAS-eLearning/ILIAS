<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

/**
 * Interface hasTitle
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasTitle extends isItem
{

    public function withTitle(string $title) : hasTitle;
    

    public function getTitle() : string;
}
