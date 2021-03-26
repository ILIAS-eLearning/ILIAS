<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */


/**
 * Class ilECSCategorySettings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
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
