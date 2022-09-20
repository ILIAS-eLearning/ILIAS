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
 * Taxonomy tree
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxonomyTree extends ilTree
{
    public function __construct(int $a_id)
    {
        parent::__construct($a_id);
        $this->setTreeTablePK("tax_tree_id");
        $this->setTableNames('tax_tree', 'tax_node');
    }
}
