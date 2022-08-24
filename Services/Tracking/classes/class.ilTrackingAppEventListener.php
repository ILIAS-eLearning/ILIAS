<?php

declare(strict_types=0);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
        }
    }
}
