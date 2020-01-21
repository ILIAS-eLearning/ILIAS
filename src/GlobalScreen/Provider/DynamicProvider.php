<?php namespace ILIAS\GlobalScreen\Provider;

/**
 * Interface DynamicProvider
 *
 * Needs JF decision whenever a new DynamicProvider is implemented.
 *
 * All dynamic providers will be asked whether there are GlobalScreen elements to append
 * for the current context. Therefore you have to implement the following
 * methods as efficient as possible. The methods will be called in the following
 * order:
 * 1. @see DynamicProvider::isGloballyAvailable
 * 2. @see DynamicProvider::isAvailableForCurrentUser
 * 3. @see DynamicProvider::isCurrentlyAvailable
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicProvider extends Provider
{

    /**
     * Return a simple bool whether your component provides dynamic elements
     * for the GlobalScreen Service.
     * Return false e.g. if your component is not active. This should not be
     * dependant from the current user.
     *
     * @return bool
     */
    public function isGloballyAvailable() : bool;


    /**
     * Return a simple bool whether the GlobalScreen element could be relevant for the
     * current logged in user. E.g. if a user is not allowed to use the
     * mail-system, return false;
     *
     * @return bool
     */
    public function isAvailableForCurrentUser() : bool;


    /**
     * ATTENTION: Implement your isCurrentlyActive()-Method as efficient as
     * possible
     *
     * @return bool
     */
    public function isCurrentlyAvailable() : bool;
}
