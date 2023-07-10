<?php

/**
 * Class ilObjMainMenuAccess
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilMainMenuAccess
{
    /**
     * @param string $permission
     * @throws ilException
     */
    public function checkAccessAndThrowException(string $permission): void;

    /**
     * @param string $permission
     * @return bool
     */
    public function hasUserPermissionTo(string $permission): bool;

    /**
     * @return array
     */
    public function getGlobalRoles(): array;

    /**
     * @param ilMMCustomItemStorage $item
     * @return Closure
     */
    public function isCurrentUserAllowedToSeeCustomItem(ilMMCustomItemStorage $item): Closure;
}
