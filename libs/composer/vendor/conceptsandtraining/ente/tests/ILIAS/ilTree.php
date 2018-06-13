<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

/**
 * Essentials of ILIAS tree for this framework.
 */
abstract class ilTree {
    /**
     * Get all ids of subnodes
     * @return 
     * @param object $a_ref_id
     */
    public function getSubTreeIds($a_ref_id) {
        assert(false);
    }

	/**
	* Returns the node path for the specified object reference.
	*
	* Note: this function returns the same result as getNodePathForTitlePath,
	* but takes ref-id's as parameters.
	*
	* This function differs from getPathFull, in the following aspects:
	* - The title of an object is not translated into the language of the user
	* - This function is significantly faster than getPathFull.
	*
	* @access	public
	* @param	integer	node_id of endnode
	* @param	integer	node_id of startnode (optional)
	* @return	array	ordered path info (depth,parent,child,obj_id,type,title)
	*               or null, if the node_id can not be converted into a node path.
	*/
	public function getNodePath($a_endnode_id, $a_startnode_id = 0) {
        assert(false);
    }
}
