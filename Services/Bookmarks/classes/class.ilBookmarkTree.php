<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tree/classes/class.ilTree.php");

/**
 * Bookmark tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilBookmarkTree extends ilTree
{
    /**
     * Constructor
     *
     * @param int $a_user_id user id
     */
    public function __construct($a_user_id)
    {
        parent::__construct($a_user_id);
        $this->setTableNames('bookmark_tree', 'bookmark_data');
    }
}
