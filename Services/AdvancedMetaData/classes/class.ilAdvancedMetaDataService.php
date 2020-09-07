<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Component/classes/class.ilService.php");

/**
 * ilAdvancedMetaDataService Service definition
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 */
class ilAdvancedMetaDataService extends ilService
{
    /**
     * @see ilComponent::getVersion()
     */
    public function getVersion()
    {
    }
    
    /**
     * @see ilComponent::isCore()
     */
    public function isCore()
    {
        return true;
    }
}
