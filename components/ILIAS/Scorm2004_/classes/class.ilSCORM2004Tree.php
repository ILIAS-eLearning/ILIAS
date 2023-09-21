<?php

declare(strict_types=1);

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
 * SCORM 2004 Editing tree
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSCORM2004Tree extends ilTree
{
    public function __construct(int $a_id)
    {
        parent::__construct($a_id);
        $this->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $this->setTreeTablePK("slm_id");
    }
}
