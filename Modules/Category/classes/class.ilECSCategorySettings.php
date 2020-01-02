<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/WebServices/ECS/classes/class.ilECSObjectSettings.php';

/**
* Class ilECSCategorySettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup Modules/Category
*/
class ilECSCategorySettings extends ilECSObjectSettings
{
    protected function getECSObjectType()
    {
        return '/campusconnect/categories';
    }
    
    protected function buildJson(ilECSSetting $a_server)
    {
        $json = $this->getJsonCore('application/ecs-category');
        
        return $json;
    }
}
