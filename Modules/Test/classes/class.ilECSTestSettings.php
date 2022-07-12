<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/WebServices/ECS/classes/class.ilECSObjectSettings.php';

/**
* Class ilECSTestSettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup ModulesTest
*/
class ilECSTestSettings extends ilECSObjectSettings
{
    protected function getECSObjectType() : string
    {
        return '/campusconnect/tests';
    }

    /** @return object|stdClass */
    protected function buildJson(ilECSSetting $a_server)
    {
        return $this->getJsonCore('application/ecs-test');
    }
}
