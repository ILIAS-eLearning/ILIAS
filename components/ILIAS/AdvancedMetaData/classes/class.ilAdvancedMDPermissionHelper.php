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
 * Advanced metadata permission helper
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDPermissionHelper extends ilClaimingPermissionHelper
{
    public const int CONTEXT_MD = 1;
    public const int CONTEXT_RECORD = 2;
    public const int CONTEXT_FIELD = 3;

    public const int ACTION_MD_CREATE_RECORD = 1;
    public const int ACTION_MD_IMPORT_RECORDS = 2;

    public const int ACTION_RECORD_EDIT = 5;
    public const int ACTION_RECORD_DELETE = 6;
    public const int ACTION_RECORD_EXPORT = 7;
    public const int ACTION_RECORD_TOGGLE_ACTIVATION = 8;
    public const int ACTION_RECORD_EDIT_PROPERTY = 9;
    public const int ACTION_RECORD_EDIT_FIELDS = 10;
    public const int ACTION_RECORD_CREATE_FIELD = 11;
    public const int ACTION_RECORD_FIELD_POSITIONS = 12;

    public const int ACTION_FIELD_EDIT = 13;
    public const int ACTION_FIELD_DELETE = 14;
    public const int ACTION_FIELD_EDIT_PROPERTY = 15;

    public const int SUBACTION_UNDEFINED = 0;
    public const int SUBACTION_RECORD_TITLE = 1;
    public const int SUBACTION_RECORD_DESCRIPTION = 2;
    public const int SUBACTION_RECORD_OBJECT_TYPES = 3;

    public const int SUBACTION_FIELD_TITLE = 4;
    public const int SUBACTION_FIELD_DESCRIPTION = 5;
    public const int SUBACTION_FIELD_SEARCHABLE = 6;
    public const int SUBACTION_FIELD_PROPERTIES = 7;

    protected function readContextIds(int $a_context_type): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        switch ($a_context_type) {
            case self::CONTEXT_MD:
                return array($this->ref_id);

            case self::CONTEXT_RECORD:
                $set = $ilDB->query("SELECT record_id id" .
                    " FROM adv_md_record");
                break;

            case self::CONTEXT_FIELD:
                $set = $ilDB->query("SELECT field_id id" .
                    " FROM adv_mdf_definition");
                break;

            default:
                return array();
        }

        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["id"];
        }
        return $res;
    }

    // permissions

    protected function buildPermissionMap(): array
    {
        return array(
            self::CONTEXT_MD => array(
                "actions" => array(
                    self::ACTION_MD_CREATE_RECORD
                    ,
                    self::ACTION_MD_IMPORT_RECORDS
                )
            ),
            self::CONTEXT_RECORD => array(
                "actions" => array(
                    self::ACTION_RECORD_EDIT
                    ,
                    self::ACTION_RECORD_DELETE
                    ,
                    self::ACTION_RECORD_EXPORT
                    ,
                    self::ACTION_RECORD_TOGGLE_ACTIVATION
                    ,
                    self::ACTION_RECORD_EDIT_FIELDS
                    ,
                    self::ACTION_RECORD_FIELD_POSITIONS
                    ,
                    self::ACTION_RECORD_CREATE_FIELD
                ),
                "subactions" => array(
                    self::ACTION_RECORD_EDIT_PROPERTY =>
                        array(
                            self::SUBACTION_RECORD_TITLE
                            ,
                            self::SUBACTION_RECORD_DESCRIPTION
                            ,
                            self::SUBACTION_RECORD_OBJECT_TYPES
                        )
                )
            ),
            self::CONTEXT_FIELD => array(
                "actions" => array(
                    self::ACTION_FIELD_EDIT,
                    self::ACTION_FIELD_DELETE
                ),
                "subactions" => array(
                    self::ACTION_FIELD_EDIT_PROPERTY =>
                        array(
                            self::SUBACTION_FIELD_TITLE
                            ,
                            self::SUBACTION_FIELD_DESCRIPTION
                            ,
                            self::SUBACTION_FIELD_SEARCHABLE
                            ,
                            self::SUBACTION_FIELD_PROPERTIES
                        )
                )
            )
        );
    }

    // plugins

    protected function getActivePlugins(): Generator
    {
        global $DIC;

        $component_factory = $DIC['component.factory'];
        yield from $component_factory->getActivePluginsInSlot("amdc");
    }

    protected function checkPermission(
        int $a_context_type,
        int $a_context_id,
        int $a_action_id,
        ?int $a_action_sub_id = null
    ): bool {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        if (!$this->checkPlugins($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id)) {
            return false;
        }

        // export is considered read-action
        if ($a_context_type == ilAdvancedMDPermissionHelper::CONTEXT_RECORD &&
            $a_action_id == ilAdvancedMDPermissionHelper::ACTION_RECORD_EXPORT) {
            return $ilAccess->checkAccessOfUser($this->getUserId(), "read", "", $this->getRefId());
        }

        return $this->checkRBAC();
    }
}
