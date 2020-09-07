<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Skill management main application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilObjSkillManagement extends ilObject
{
    
    /**
     * Constructor
     * @access	public
     * @param	integer	reference_id or object_id
     * @param	boolean	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "skmg";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update()
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
    public function read()
    {
        $ilDB = $this->db;

        parent::read();
    }

    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
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
     * @param	string	$source_id		Source element ID
     * @param	string	$target_id		Target element ID
     * @param	string	$first_child	Insert as first child of target
     * @param	string	$movecopy		Position ("move" | "copy")
     */
    public function executeDragDrop($source_id, $target_id, $first_child, $as_subitem = false, $movecopy = "move")
    {
        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $tree = new ilSkillTree();

        include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");

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
            $subnodes = $tree->getSubtree($source_node);

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
