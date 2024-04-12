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

declare(strict_types=1);

/**
 * Event listener for cmix. Has the following tasks:
 *
 * @author  Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 *
 */

class ilCmiXapiAppEventListener
{
    /**
     * @throws ilException
     */
    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        switch ($component) {
            case "Services/User":
                if ($event == "deleteUser") {
                    self::onServiceUserDeleteUser($parameter);
                }
                break;
            case "Services/Object":
                switch ($event) {
                    case "delete":
                    case "toTrash":
                        self::onServiceObjectDeleteOrToTrash($parameter);
                        break;
                }
                break;

            case "Modules/Course":
                if ($event == "deleteParticipant") {
                    self::removeMembers(
                        'crs',
                        $parameter
                    );
                }
                break;
            case "Modules/Group":
                if ($event == "deleteParticipant") {
                    self::removeMembers(
                        'grp',
                        $parameter
                    );
                }
                break;

            default:
                throw new ilException(
                    "ilCmiXapiAppEventListener::handleEvent: Won't handle events of '$component'."
                );
        }
    }

    private static function onServiceUserDeleteUser(array $parameter): void
    {
        $usr_id = $parameter['usr_id'];
        $model = ilCmiXapiDelModel::init();

        // null or array with objIds, if are going to need more
        $xapiObjUser = $model->getXapiObjIdForUser($usr_id);
        if(!is_null($xapiObjUser)) {
            for ((int) $i = 0; $i < count($xapiObjUser); $i++) {
                $xapiObject = $model->getXapiObjectData($xapiObjUser[$i]);
                if(!is_null($xapiObject)) {
                    if ((int) $xapiObject['delete_data'] != 0) {
                        if((int) $xapiObject['delete_data'] < 10) {
                            //remove only ident
                            $model->removeCmixUsersForObjectAndUser($xapiObjUser[$i], $usr_id);
                        } else {
                            // add obj as deleted
                            $model->setXapiObjAsDeletedForUser($xapiObjUser[$i], $xapiObject['lrs_type_id'], $xapiObject['activity_id'], $usr_id);
                        }
                    }
                }
            }
        }

        //       if(!is_null($xapiObjUser)) {
        //            // add user as deleted
        //            $model->setXapiUserAsDeleted($usr_id);
        //        }
    }

    private static function onServiceObjectDeleteOrToTrash(array $parameter): void
    {
        if (ilObject::_lookupType((int) $parameter["ref_id"], true) !== "cmix") {
            return;
        }

        $model = ilCmiXapiDelModel::init();
        $objId = (int) $parameter['obj_id'];
        $xapiObject = $model->getXapiObjectData($objId);

        if(!is_null($xapiObject)) {
            if ((int) $xapiObject['delete_data'] != 0) {
                if((int) $xapiObject['delete_data'] < 10) {
                    //remove only ident
                    $model->removeCmixUsersForObject($objId);
                } else {
                    // add obj as deleted
                    $model->setXapiObjAsDeleted($objId, $xapiObject['lrs_type_id'], $xapiObject['activity_id']);
                }
            }
        }
    }

    private static function removeMembers(string $src_type, array $parameter): void
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        $usr_id = $parameter['usr_id'];
        $crs_id = $parameter['obj_id'];
        if (
            $src_type === 'grp' || $src_type === 'crs'
        ) {
            $crs_ref_ids = ilObject::_getAllReferences($crs_id);
            $idc = array_shift($crs_ref_ids);

            //Todo check Verknüpfungen?
            $ref_ids = $tree->getSubTreeIds($idc);
            for ((int) $i = 0; $i < count($ref_ids); $i++) {
                if (ilObject::_lookupType($ref_ids[$i], true) == "cmix") {
                    $objId = ilObject::_lookupObjectId($ref_ids[$i]);
                    $model = ilCmiXapiDelModel::init();
                    $xapiObject = $model->getXapiObjectData($objId);
                    if(!is_null($xapiObject)) {
                        $xapiObjUser = $model->getXapiObjIdForUser($usr_id);
                        if(!is_null($xapiObjUser)) {
                            // add user as deleted
                            if ((int) $xapiObject['delete_data'] != 0) {
                                if ((int) $xapiObject['delete_data'] < 10) {
                                    //remove only ident
                                    $model->removeCmixUsersForObjectAndUser($objId, $usr_id);
                                } else {
                                    // add obj as deleted
                                    $model->setXapiObjAsDeletedForUser($objId, $xapiObject['lrs_type_id'], $xapiObject['activity_id'], $usr_id);
                                }
                            }
                        }
                    }
                }
            }
        }
    }


}
