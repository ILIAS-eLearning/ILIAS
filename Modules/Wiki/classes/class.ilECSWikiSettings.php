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
 * Class ilECSWikiSettings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSWikiSettings extends ilECSObjectSettings
{
    protected function getECSObjectType() : string
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
