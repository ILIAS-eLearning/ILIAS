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
 ********************************************************************
 */

/**
 * Skill management main application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjSkillManagement extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "skmg";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function update() : bool
    {
        $ilDB = $this->db;
        
        if (!parent::update()) {
            return false;
        }

        return true;
    }
    
    /**
    * read style folder data
    */
    public function read() : void
    {
        $ilDB = $this->db;

        parent::read();
    }

    /**
    * delete object and all related data
    *
    * @return	bool	true if all object data were removed; false if only a references were removed
    */
    public function delete() : bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        
        //put here your module specific stuff
        
        return true;
    }

    /**
     * Execute Drag Drop Action
     *
     * @param	int		$source_id		Source element ID
     * @param	int		$target_id		Target element ID
     * @param	bool	$first_child	Insert as first child of target
     * @param	bool	$as_subitem 	Insert as last child of target
     * @param	string	$movecopy		Position ("move" | "copy")
     */
    public function executeDragDrop(
        int $source_id,
        int $target_id,
        bool $first_child,
        bool $as_subitem = false,
        string $movecopy = "move"
    ) : void {
        $tree = new ilSkillTree();

        $source_obj = ilSkillTreeNodeFactory::getInstance($source_id);

        if (!$first_child) {
            $target_obj = ilSkillTreeNodeFactory::getInstance($target_id);
            $target_parent = $tree->getParentId($target_id);
        }
        // handle skills
        if ($source_obj->getType() == "skll") {
            if ($tree->isInTree($source_obj->getId())) {
                $node_data = $tree->getNodeData($source_obj->getId());

                // cut on move
                if ($movecopy == "move") {
                    $parent_id = $tree->getParentId($source_obj->getId());
                    $tree->deleteTree($node_data);
                }

                // paste page
                if (!$tree->isInTree($source_obj->getId())) {
                    if ($first_child) {			// as first child
                        $target_pos = IL_FIRST_NODE;
                        $parent = $target_id;
                    } elseif ($as_subitem) {		// as last child
                        $parent = $target_id;
                        $target_pos = IL_FIRST_NODE;
                        $childs = $tree->getChildsByType($parent, array("skll", "scat"));
                        if (count($childs) != 0) {
                            $target_pos = $childs[count($childs) - 1]["obj_id"];
                        }
                    } else {						// at position
                        $target_pos = $target_id;
                        $parent = $target_parent;
                    }
                    // insert skill into tree
                    $tree->insertNode(
                        $source_obj->getId(),
                        $parent,
                        $target_pos
                    );
                }
            }
        }

        // handle skil categories
        if ($source_obj->getType() == "scat") {
            $source_node = $tree->getNodeData($source_id);
            $subnodes = $tree->getSubTree($source_node);

            // check, if target is within subtree
            foreach ($subnodes as $subnode) {
                if ($subnode["obj_id"] == $target_id) {
                    return;
                }
            }

            $target_pos = $target_id;

            if ($first_child) {		// as first node
                $target_pos = IL_FIRST_NODE;
                $target_parent = $target_id;
            } elseif ($as_subitem) {		// as last node
                $target_parent = $target_id;
                $target_pos = IL_FIRST_NODE;
                $childs = $tree->getChilds($target_parent);
                if (count($childs) != 0) {
                    $target_pos = $childs[count($childs) - 1]["obj_id"];
                }
            }

            // delete source tree
            if ($movecopy == "move") {
                $tree->deleteTree($source_node);
            }

            if (!$tree->isInTree($source_id)) {
                $tree->insertNode($source_id, $target_parent, $target_pos);

                // insert moved tree
                if ($movecopy == "move") {
                    foreach ($subnodes as $node) {
                        if ($node["obj_id"] != $source_id) {
                            $tree->insertNode($node["obj_id"], $node["parent"]);
                        }
                    }
                }
            }
        }
    }
}
