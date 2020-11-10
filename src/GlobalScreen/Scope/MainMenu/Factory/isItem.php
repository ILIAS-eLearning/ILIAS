<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\GlobalScreen\Scope\isGlobalScreenItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Interface IFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isItem extends isGlobalScreenItem
{

    /**
     * Pass a callable which can decide whether your element is visible for
     * the current user
     * @param callable $is_visible
     * @return isItem|isChild
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
     * @param callable $is_avaiable
     * @return isItem|isChild
     */
    public function withAvailableCallable(callable $is_avaiable) : isItem;

    /**
     * @return bool
     */
    public function isAvailable() : bool;

    /**
     * If your provider or the service which provides the Item does not allow
     * to activate the item (@param Legacy $element
     * @return isItem
     * @see withAvailableCallable ), please provide the
     *      reason why. You can pass e Legacy Component for the moment, in most cases
     *      this will be something like in
     *      Services/Administration/templates/default/tpl.external_settings.html
     */
    public function withNonAvailableReason(Legacy $element) : isItem;

    /**
     * @return Legacy
     */
    public function getNonAvailableReason() : Legacy;

    /**
     * Return the default position for installation, this will be overridden by
     * the configuration later
     * @return int
     */
    public function getPosition() : int;

    /**
     * @param int $position
     * @return isItem
     */
    public function withPosition(int $position) : isItem;

    /**
     * @return bool
     */
    public function isAlwaysAvailable() : bool;

    /**
     * @param bool $always_active
     * @return isItem
     */
    public function withAlwaysAvailable(bool $always_active) : isItem;

    /**
     * @param TypeInformation $information
     * @return isItem
     */
    public function setTypeInformation(TypeInformation $information) : isItem;

    /**
     * @return TypeInformation
     */
    public function getTypeInformation() : TypeInformation;

    public function isTop() : bool;
}
