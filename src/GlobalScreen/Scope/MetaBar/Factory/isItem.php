<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class isItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isItem
{

    /**
     * @return IdentificationInterface
     */
    public function getProviderIdentification() : IdentificationInterface;


    /**
     * Pass a callable which can decide whether your element is visible for
     * the current user
     *
     * @param callable $is_visible
     *
     * @return isItem
     */
    public function withVisibilityCallable(callable $is_visible) : isItem;


    /**
     * @return bool
     */
    public function isVisible() : bool;


    /**
     * Pass a callable which can decide wheter your element is available in
     * general, e.g. return false for the Badges Item when the Badges-Service
     * is disabled.
     *
     * @param callable $is_avaiable
     *
     * @return isItem
     */
    public function withAvailableCallable(callable $is_avaiable) : isItem;


    /**
     * @return bool
     */
    public function isAvailable() : bool;


    /**
     * Return the default position for installation, this will be overridden by
     * the configuration later
     *
     * @return int
     */
    public function getPosition() : int;


    /**
     * @param int $position
     *
     * @return isItem
     */
    public function withPosition(int $position) : isItem;
}
