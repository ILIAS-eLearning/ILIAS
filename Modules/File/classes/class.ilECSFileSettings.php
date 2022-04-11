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
 * Class ilECSFileSettings
 *
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
 *
 * @ingroup Modules/File
 */
class ilECSFileSettings extends ilECSObjectSettings
{
    protected function getECSObjectType() : string
    {
        return '/campusconnect/files';
    }


    protected function buildJson(ilECSSetting $a_server)
    {
        $json = $this->getJsonCore('application/ecs-file');
        $json->version = $this->content_obj->getVersion();
        $entries = ilHistory::_getEntriesForObject(
            $this->content_obj->getId(),
            $this->content_obj->getType()
        );
        if ($entries !== []) {
            $entry = array_shift($entries);
            $entry = new ilDateTime($entry["date"], IL_CAL_DATETIME);

            $json->version_date = $entry->get(IL_CAL_UNIX);
        } else {
            $json->version_date = time();
        }

        return $json;
    }
}
