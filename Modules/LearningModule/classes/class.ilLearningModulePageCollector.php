<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/interfaces/interface.ilCOPageCollectorInterface.php");

/**
 * Page collector for learning modules
 *
 * @author killing@leifos.de
 * @ingroup ServicesCOPage
 */
class ilLearningModulePageCollector implements ilCOPageCollectorInterface
{
    /**
     * @inheritdoc
     */
    public function getAllPageIds($obj_id)
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
