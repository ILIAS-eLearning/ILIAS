<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    * @param int $a_id tree id (= SCORM Learning Module Object ID)
    */
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setTableNames('scorm_tree', 'scorm_object');
        $this->setTreeTablePK('slm_id');
    }
    
    /**
    * get child nodes of given node
    * @param string $a_order	sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
    * @param string $a_direction	sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
    * @return array with node data of all childs or empty array
    * @throws InvalidArgumentException
    */
    public function getChilds(int $a_node_id, string $a_order = "", string $a_direction = "ASC") : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->logger->error($message);
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
        }

        return $childs;
    }
}
