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

include_once("./Services/ContainerReference/classes/class.ilContainerReferenceAccess.php");

/**
*
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ModulesCourseReference
*/

class ilObjCourseReferenceAccess extends ilContainerReferenceAccess
{
    /**
     * @inheritdoc
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        global $DIC;

        switch ($permission) {
            case 'visible':
            case 'read':
            case 'edit_learning_progress':
                include_once './Modules/CourseReference/classes/class.ilObjCourseReference.php';
                $target_ref_id = ilObjCourseReference::_lookupTargetRefId($obj_id);

                if (!$target_ref_id || !$DIC->access()->checkAccessOfUser($user_id, $permission, $cmd, $target_ref_id)) {
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function _preloadData(array $obj_ids, array $ref_ids): void
    {
        global $DIC;

        $repository = new ilUserCertificateRepository();
        $coursePreload = new ilCertificateObjectsForUserPreloader($repository);
        $coursePreload->preLoad($DIC->user()->getId(), array_map(function ($objId) {
            return (int) \ilObjCourseReference::_lookupTargetId($objId);
        }, $obj_ids));
    }

    /**
     * @inheritdoc
     */
    public static function _getCommands($a_ref_id = 0): array
    {
        global $DIC;

        if ($DIC->access()->checkAccess('write', '', $a_ref_id)) {
            // Only local (reference specific commands)
            $commands = array(
                array("permission" => "visible", "cmd" => "", "lang_var" => "show","default" => true),
                array("permission" => "write", "cmd" => "editReference", "lang_var" => "edit")
            );
        } else {
            include_once('./Modules/Course/classes/class.ilObjCourseAccess.php');
            $commands = ilObjCourseAccess::_getCommands();
        }
        return $commands;
    }
}
