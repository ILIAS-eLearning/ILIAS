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

class ilPageObjectFactory
{
    public static function getInstance(
        string $a_parent_type,
        int $a_id = 0,
        int $a_old_nr = 0,
        string $a_lang = "-"
    ): ilPageObject {
        $def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
        $class = $def["class_name"];
        $obj = new $class($a_id, $a_old_nr, $a_lang);

        return $obj;
    }
}
