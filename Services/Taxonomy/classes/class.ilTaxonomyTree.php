<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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
