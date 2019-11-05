<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use Closure;

/**
 * Interface hasActions
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasActions
{

    //
    // TODO
    // - additional actions ev. auch als callback, beide varianten (auch URl) anbieten
    //

    /**
     * @param string $action
     *
     * @return isItem
     */
    public function withAction(string $action) : isItem;


    /**
     * @return bool
     */
    public function hasAction() : bool;


    /**
     * @return string
     */
    public function getAction() : string;


    /**
     * @param Closure $callback
     *
     * @return isItem
     */
    public function withCloseActionCallback(Closure $callback) : isItem;


    /**
     * @return Closure
     */
    public function getCloseActionCallback() : Closure;


    /**
     * @param string $title
     * @param string $action
     *
     * @return isItem
     */
    public function withAdditionalAction(string $title, string $action) : isItem;


    /**
     * @return array
     */
    public function getAdditionalActions() : array;
}
