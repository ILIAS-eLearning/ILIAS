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
 * Badge type interface
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
interface ilBadgeType
{
    /**
     * Get typ id (unique for component)
     */
    public function getId() : string;

    public function getCaption() : string;

    /**
     * Can only be created once?
     */
    public function isSingleton() : bool;

    /**
     * Get valid (repository) "parent" object types
     * @return string[]
     */
    public function getValidObjectTypes() : array;
        
    /**
     * Get GUI config instance
     */
    public function getConfigGUIInstance() : ?ilBadgeTypeGUI;
}
