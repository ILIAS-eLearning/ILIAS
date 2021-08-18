<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilECSWikiSettings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
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
