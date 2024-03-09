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

namespace ILIAS\MediaPool\Tree;

class MediaPoolTree extends \ilTree
{
    public function __construct(int $mep_obj_id)
    {
        parent::__construct($mep_obj_id);
        $this->setTreeTablePK("mep_id");
        $this->setTableNames("mep_tree", "mep_item");
    }

    public function insertInMepTree(
        int $a_obj_id,
        ?int $a_parent = null
    ): void {
        if (!$this->isInTree($a_obj_id)) {
            $parent = (is_null($a_parent))
                ? $this->getRootId()
                : $a_parent;
            $this->insertNode($a_obj_id, $parent);
        }
    }

}
