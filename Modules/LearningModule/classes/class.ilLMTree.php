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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMTree extends ilTree
{
    public static array $instances = [];
    protected ?array $complete_tree = null;

    public function __construct(
        int $a_tree_id,
        bool $read_root_id = true
    ) {
        parent::__construct($a_tree_id);
        $this->setTableNames('lm_tree', 'lm_data');
        $this->setTreeTablePK("lm_id");
        $this->useCache(true);
        if ($read_root_id) {
            $this->readRootId();
        }
    }

    public static function getInstance(
        int $a_tree_id
    ) : self {
        if (isset(self::$instances[$a_tree_id])) {
            return self::$instances[$a_tree_id];
        }
        $tree = new ilLMTree($a_tree_id);
        self::$instances[$a_tree_id] = $tree;

        return $tree;
    }

    public function isCacheUsed() : bool
    {
        return $this->use_cache;
    }

    public function getLastActivePage() : int
    {
        $ilDB = $this->db;
        
        $ilDB->setLimit(1, 0);
        
        $sql = "SELECT lm_data.obj_id" .
            " FROM lm_data" .
            " JOIN lm_tree ON (lm_tree.child = lm_data.obj_id)" .
            " JOIN page_object ON (page_object.page_id = lm_data.obj_id AND page_object.parent_type = " . $ilDB->quote("lm", "text") . ")" .
            " WHERE lm_tree.lm_id = " . $ilDB->quote($this->tree_id, "integer") .
            " AND lm_data.type = " . $ilDB->quote("pg", "text") .
            " AND page_object.active = " . $ilDB->quote(1, "integer") .
            " ORDER BY lm_tree.rgt DESC";
        $set = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["obj_id"];
    }

    /**
     * Get complete tree
     */
    public function getCompleteTree() : array
    {
        if (is_null($this->complete_tree)) {
            $this->complete_tree = $this->getSubTree($this->getNodeData($this->readRootId()));
        }
        return $this->complete_tree;
    }
}
