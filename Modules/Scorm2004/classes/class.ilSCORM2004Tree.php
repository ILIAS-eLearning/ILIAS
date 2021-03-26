<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * SCORM 2004 Editing tree
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSCORM2004Tree extends ilTree
{
    /**
     * Constructor
     */
    public function __construct($a_id)
    {
        parent::__construct($a_id);
        $this->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $this->setTreeTablePK("slm_id");
    }
}
