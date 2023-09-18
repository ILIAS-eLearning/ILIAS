<?php

declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    protected function getECSObjectType(): string
    {
        return '/campusconnect/groups';
    }

    /**
     * @return object|stdClass
     */
    protected function buildJson(ilECSSetting $a_server)
    {
        return $this->getJsonCore('application/ecs-group');
    }
}
