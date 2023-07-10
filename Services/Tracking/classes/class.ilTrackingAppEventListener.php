<?php

declare(strict_types=0);

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
 * Update lp data from Services/Object events
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilTrackingAppEventListener implements ilAppEventListener
{
    /**
     * Handle an event in a listener.
     * @param string $a_component component, e.g. "Modules/Forum" or "Services/User"
     * @param string $a_event     event e.g. "createUser", "updateUser", "deleteUser", ...
     * @param array  $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
     */
    public static function handleEvent(
        string $a_component,
        string $a_event,
        array $a_parameter
    ): void {
        $obj_id = $a_parameter['obj_id'] ?? null;
        switch ($a_component) {
            case 'Services/Object':
                switch ($a_event) {
                    case 'toTrash':
                        $olp = ilObjectLP::getInstance($obj_id);
                        $olp->handleToTrash();
                        break;

                    case 'delete':
                        // ilRepUtil will raise "delete" even if only reference was deleted!
                        $all_ref = ilObject::_getAllReferences($obj_id);
                        if (!sizeof($all_ref)) {
                            $olp = ilObjectLP::getInstance($obj_id);
                            $olp->handleDelete();
                        }
                        break;
                }
                break;

            case 'Services/Tree':
                switch ($a_event) {
                    case 'moveTree':
                        if ($a_parameter['tree'] == 'tree') {
                            ilObjectLP::handleMove($a_parameter['source_id']);
                        }
                        break;
                }
                break;

            case 'Modules/Group':
            case 'Modules/Course':
            case 'Modules/LearningSequence':
                switch ($a_event) {
                    case 'addParticipant':
                        ilLPStatusWrapper::_updateStatus((int) $obj_id, (int) ($a_parameter['usr_id'] ?? 0));
                        break;
                }
        }
    }
}
