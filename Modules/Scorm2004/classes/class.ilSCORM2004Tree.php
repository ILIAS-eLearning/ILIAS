<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tree/classes/class.ilTree.php");

/**
 * SCORM 2004 Editing tree
 *
 * @author Alex Killing <alex.kiling@gmx.de>
 * @version $Id$
 * @ingroup ModulesScorm2004
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
