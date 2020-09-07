<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/WebServices/ECS/classes/class.ilECSObjectSettings.php';

/**
* Class ilECSGroupSettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup ModulesGroup
*/
class ilECSGroupSettings extends ilECSObjectSettings
{
    protected function getECSObjectType()
    {
        return '/campusconnect/groups';
    }
    
    protected function buildJson(ilECSSetting $a_server)
    {
        $json = $this->getJsonCore('application/ecs-group');
        
        // $json->status = $this->content_obj->isActivated() ? 'online' : 'offline';
        
        return $json;
    }
}
