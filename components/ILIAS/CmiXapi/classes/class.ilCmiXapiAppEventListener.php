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
    /** @var array<int, array<int, true>> */
    private static array $containerXapiObjectIds = [];

    /**
     * @throws ilException
     * @param array<string, mixed> $parameter
     */
    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        switch ($component) {
            case "components/ILIAS/User":
                if ($event === "deleteUser") {
                    self::onServiceUserDeleteUser($parameter);
                }
                break;
            case "components/ILIAS/ILIASObject":
                switch ($event) {
                    case "delete":
                    case "toTrash":
                        self::onServiceObjectDeleteOrToTrash($parameter);
                        break;
                }
                break;

            case "components/ILIAS/Course":
                if ($event === "deleteParticipant") {
                    self::removeMember($parameter);
                }
                break;
            case "components/ILIAS/Group":
                if ($event === "deleteParticipant") {
                    self::removeMember($parameter);
                }
                break;

            default:
                throw new ilException(
                    "ilCmiXapiAppEventListener::handleEvent: Won't handle events of '$component'."
                );
        }
    }

    /**
     * @param array<string, mixed> $parameter
     */
    private static function onServiceUserDeleteUser(array $parameter): void
    {
        $usr_id = (int) $parameter['usr_id'];
        $model = ilCmiXapiDelModel::init();

        foreach ($model->getXapiObjectsByUser($usr_id) as $xapiObject) {
            self::deleteUserDataForObject($model, $xapiObject, $usr_id);
        }
    }

    /**
     * @param array<string, mixed> $parameter
     */
    private static function onServiceObjectDeleteOrToTrash(array $parameter): void
    {
        if (ilObject::_lookupType((int) $parameter["ref_id"], true) !== "cmix") {
            return;
        }

        $model = ilCmiXapiDelModel::init();
        $objId = (int) $parameter['obj_id'];
        $xapiObject = $model->getXapiObjectData($objId);

        if ($xapiObject === null || (int) $xapiObject['delete_data'] === 0) {
            return;
        }

        if ((int) $xapiObject['delete_data'] < 10) {
            $model->removeCmixUsersForObject($objId);
            return;
        }

        $model->setXapiObjAsDeleted(
            $objId,
            (int) $xapiObject['lrs_type_id'],
            (string) $xapiObject['activity_id']
        );
    }

    /**
     * @param array<string, mixed> $parameter
     */
    private static function removeMember(array $parameter): void
    {
        $usr_id = (int) $parameter['usr_id'];
        $container_id = (int) $parameter['obj_id'];
        $xapiObjectIds = self::getContainerXapiObjectIds($container_id);
        if ($xapiObjectIds === []) {
            return;
        }

        $model = ilCmiXapiDelModel::init();
        foreach ($model->getXapiObjectsByUser($usr_id) as $xapiObject) {
            if (isset($xapiObjectIds[(int) $xapiObject['obj_id']])) {
                self::deleteUserDataForObject($model, $xapiObject, $usr_id);
            }
        }
    }

    /**
     * @return array<int, true>
     */
    private static function getContainerXapiObjectIds(int $containerId): array
    {
        if (isset(self::$containerXapiObjectIds[$containerId])) {
            return self::$containerXapiObjectIds[$containerId];
        }

        global $DIC;
        $tree = $DIC->repositoryTree();
        $xapiObjectIds = [];

        foreach (ilObject::_getAllReferences($containerId) as $containerRefId) {
            $containerNode = $tree->getNodeData($containerRefId);
            if ($containerNode === []) {
                continue;
            }

            foreach ($tree->getSubTree($containerNode, false, ['cmix']) as $xapiRefId) {
                $xapiObjectIds[ilObject::_lookupObjectId($xapiRefId)] = true;
            }
        }

        return self::$containerXapiObjectIds[$containerId] = $xapiObjectIds;
    }

    /**
     * @param array{
     *     obj_id: int|string,
     *     lrs_type_id: int|string,
     *     activity_id: string,
     *     delete_data: int|string
     * } $xapiObject
     */
    private static function deleteUserDataForObject(
        ilCmiXapiDelModel $model,
        array $xapiObject,
        int $usrId
    ): void {
        $deleteData = (int) $xapiObject['delete_data'];
        if ($deleteData === 0) {
            return;
        }

        $objId = (int) $xapiObject['obj_id'];
        if ($deleteData < 10) {
            $model->removeCmixUsersForObjectAndUser($objId, $usrId);
            return;
        }

        $model->setXapiObjAsDeletedForUser(
            $objId,
            (int) $xapiObject['lrs_type_id'],
            (string) $xapiObject['activity_id'],
            $usrId
        );
    }
}
