<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/WebServices/ECS/classes/class.ilECSObjectSettings.php';

/**
* Class ilECSWikiSettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup Modules/Wiki
*/
class ilECSWikiSettings extends ilECSObjectSettings
{
    protected function getECSObjectType()
    {
        return '/campusconnect/wikis';
    }
    
    protected function buildJson(ilECSSetting $a_server)
    {
        $json = $this->getJsonCore('application/ecs-wiki');
        
        $json->availability = $this->content_obj->getOnline() ? 'online' : 'offline';
        
        return $json;
    }
}
