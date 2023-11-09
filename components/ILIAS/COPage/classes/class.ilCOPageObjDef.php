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
 * COPage page object definition handler
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPageObjDef
{
    public static ?array $page_obj_def = null;

    public static function init(): void
    {
        global $DIC;

        $db = $DIC->database();

        if (self::$page_obj_def == null) {
            $set = $db->query("SELECT * FROM copg_pobj_def ");
            while ($rec = $db->fetchAssoc($set)) {
                self::$page_obj_def[$rec["parent_type"]] = $rec;
            }
        }
    }

    /**
     * Get all definitios
     */
    public function getDefinitions(): array
    {
        self::init();
        return self::$page_obj_def;
    }

    /**
     * Get definition by parent type
     */
    public static function getDefinitionByParentType(string $a_parent_type): array
    {
        self::init();
        return self::$page_obj_def[$a_parent_type];
    }
}
