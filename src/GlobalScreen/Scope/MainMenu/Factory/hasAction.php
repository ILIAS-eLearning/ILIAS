<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface hasAction
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasAction
{

    /**
     * @param string $action
     *
     * @return hasAction
     */
    public function withAction(string $action) : hasAction;


    /**
     * @return string
     */
    public function getAction() : string;


    /**
     * @param bool $is_external
     *
     * @return hasAction
     */
    public function withIsLinkToExternalAction(bool $is_external) : hasAction;


    /**
     * @return bool
     */
    public function isLinkWithExternalAction() : bool;
}
