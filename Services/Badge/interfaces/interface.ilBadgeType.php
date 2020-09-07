<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Badge type interface
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ServicesBadge
 */
interface ilBadgeType
{
    /**
     * Get typ id (unique for component)
     *
     * @return string
     */
    public function getId();
    
    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption();
    
    /**
     * Can only be created once?
     *
     * @return bool
     */
    public function isSingleton();

    /**
     * Get valid (repository) "parent" object types
     *
     * @return array
     */
    public function getValidObjectTypes();
        
    /**
     * Get GUI config instance
     *
     * @return ilBadgeTypeGUI|null
     */
    public function getConfigGUIInstance();
}
