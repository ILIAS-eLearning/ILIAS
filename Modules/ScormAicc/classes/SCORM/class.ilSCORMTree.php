<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once("./Services/Tree/classes/class.ilTree.php");

/**
* SCORM Object Tree
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMTree extends ilTree
{

    /**
    * Constructor
    *
    * @param	int		$a_id		tree id (= SCORM Learning Module Object ID)
    * @access	public
    */
    public function __construct($a_id = 0)
    {
        parent::__construct($a_id);
        $this->setTableNames('scorm_tree', 'scorm_object');
        $this->setTreeTablePK('slm_id');
    }
    
    /**
    * get child nodes of given node
    * @access	public
    * @param	integer		node_id
    * @param	string		sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
    * @param	string		sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
    * @return	array		with node data of all childs or empty array
    * @throws InvalidArgumentException
    */
    public function getChilds($a_node_id, $a_order = "", $a_direction = "ASC")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        // init childs
        $childs = array();

        // number of childs
        $count = 0;

        // init order_clause
        $order_clause = "";

        // set order_clause if sort order parameter is given
        if (!empty($a_order)) {
            $order_clause = "ORDER BY " . $a_order . " " . $a_direction;
        } else {
            $order_clause = "ORDER BY " . $this->table_tree . ".lft";
        }

        //666
    
        $r = $ilDB->queryF(
            "
			SELECT * FROM " . $this->table_tree . " " .
            $this->buildJoin() .
            "WHERE parent = %s " .
            "AND " . $this->table_tree . "." . $this->tree_pk . " = %s " .
            $order_clause,
            array('integer','integer'),
            array($a_node_id,$this->tree_id)
        );
    
        $count = $ilDB->numRows($r);

        if ($count > 0) {
            while ($row = $ilDB->fetchAssoc($r)) {
                $childs[] = $this->fetchNodeData($row);
            }

            // mark the last child node (important for display)
            $childs[$count - 1]["last"] = true;

            return $childs;
        } else {
            return $childs;
        }
    }
}
