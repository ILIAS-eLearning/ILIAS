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
 * UDF permission helper
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilUDFPermissionHelper extends ilClaimingPermissionHelper
{
    public const CONTEXT_UDF = 1;
    public const CONTEXT_FIELD = 2;

    public const ACTION_UDF_CREATE_FIELD = 1;

    public const ACTION_FIELD_EDIT = 1;
    public const ACTION_FIELD_DELETE = 2;
    public const ACTION_FIELD_EDIT_PROPERTY = 3;
    public const ACTION_FIELD_EDIT_ACCESS = 4;

    public const SUBACTION_FIELD_TITLE = 1;
    public const SUBACTION_FIELD_PROPERTIES = 2;

    public const SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL = 1;
    public const SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION = 2;
    public const SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL = 3;
    public const SUBACTION_FIELD_ACCESS_VISIBLE_COURSES = 4;
    public const SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS = 5;
    public const SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL = 6;
    public const SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL = 7;
    public const SUBACTION_FIELD_ACCESS_REQUIRED = 8;
    public const SUBACTION_FIELD_ACCESS_EXPORT = 9;
    public const SUBACTION_FIELD_ACCESS_SEARCHABLE = 10;
    public const SUBACTION_FIELD_ACCESS_CERTIFICATE = 11;


    // caching

    protected function readContextIds(int $a_context_type): array // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        switch ($a_context_type) {
            case self::CONTEXT_UDF:
                return array($this->getRefId());

            case self::CONTEXT_FIELD:
                $set = $ilDB->query("SELECT field_id id" .
                    " FROM udf_definition");
                break;

            default:
                return array();
        }

        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["id"];
        }
        return $res;
    }


    // permissions

    protected function buildPermissionMap(): array // Missing array type.
    {
        return array(
            self::CONTEXT_UDF => array(
                "actions" => array(
                    self::ACTION_UDF_CREATE_FIELD
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
                            ,self::SUBACTION_FIELD_PROPERTIES
                        )
                    ,self::ACTION_FIELD_EDIT_ACCESS =>
                        array(
                            self::SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL
                            ,self::SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION
                            ,self::SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL
                            ,self::SUBACTION_FIELD_ACCESS_VISIBLE_COURSES
                            ,self::SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS
                            ,self::SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL
                            ,self::SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL
                            ,self::SUBACTION_FIELD_ACCESS_REQUIRED
                            ,self::SUBACTION_FIELD_ACCESS_EXPORT
                            ,self::SUBACTION_FIELD_ACCESS_SEARCHABLE
                            ,self::SUBACTION_FIELD_ACCESS_CERTIFICATE
                        )
                )
            )
        );
    }


    // plugins

    protected function getActivePlugins(): array // Missing array type.
    {
        global $DIC;
        $component_factory = $DIC["component.factory"];
        return iterator_to_array($component_factory->getActivePluginsInSlot("udfc"));
    }
}
