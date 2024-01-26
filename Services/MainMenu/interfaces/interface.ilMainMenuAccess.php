<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function checkAccessAndThrowException(string $permission) : void;

    /**
     * @param string $permission
     * @return bool
     */
    public function hasUserPermissionTo(string $permission) : bool;

    /**
     * @return array
     */
    public function getGlobalRoles() : array;

    /**
     * @param ilMMCustomItemStorage $item
     * @return Closure
     */
    public function isCurrentUserAllowedToSeeCustomItem(ilMMCustomItemStorage $item, Closure $current) : Closure;
}
