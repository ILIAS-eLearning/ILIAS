<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/WebServices/ECS/classes/class.ilECSObjectSettings.php';

/**
 * Class ilECSFileSettings
 *
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
 *
 * @ingroup Modules/File
 */
class ilECSFileSettings extends ilECSObjectSettings
{
    protected function getECSObjectType()
    {
        return '/campusconnect/files';
    }


    protected function buildJson(ilECSSetting $a_server)
    {
        $json = $this->getJsonCore('application/ecs-file');
        $json->version = $this->content_obj->getVersion();

        require_once("./Services/History/classes/class.ilHistory.php");
        $entries = ilHistory::_getEntriesForObject(
            $this->content_obj->getId(),
            $this->content_obj->getType()
        );
        if (count($entries)) {
            $entry = array_shift($entries);
            $entry = new ilDateTime($entry["date"], IL_CAL_DATETIME);

            $json->version_date = $entry->get(IL_CAL_UNIX);
        } else {
            $json->version_date = time();
        }

        return $json;
    }
}
