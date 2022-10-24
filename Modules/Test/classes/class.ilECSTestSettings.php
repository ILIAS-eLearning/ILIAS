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
* Class ilECSTestSettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup ModulesTest
*/
class ilECSTestSettings extends ilECSObjectSettings
{
    protected function getECSObjectType(): string
    {
        return '/campusconnect/tests';
    }

    /** @return object|stdClass */
    protected function buildJson(ilECSSetting $a_server)
    {
        return $this->getJsonCore('application/ecs-test');
    }
}
