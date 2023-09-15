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
 * Page collector for learning modules
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningModulePageCollector implements ilCOPageCollectorInterface
{
    public function getAllPageIds(int $obj_id): array
    {
        $pages = [];
        foreach (ilPageObject::getAllPages("lm", $obj_id) as $p) {
            $pages[] = [
                "parent_type" => "lm",
                "id" => $p["id"],
                "lang" => $p["lang"]
            ];
        }
        return $pages;
    }
}
