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

namespace ILIAS\Skill\Tree;

use ilArrayUtil;

/**
 * Skill tree manager
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillTreeNodeManager
{
    protected int $skill_tree_id = 0;
    protected \ilSkillTree $tree;
    protected \ilObjUser $user;

    public function __construct(int $skill_tree_id, SkillTreeFactory $tree_factory)
    {
        global $DIC;

        $this->user = $DIC->user();

        $this->skill_tree_id = $skill_tree_id;
        $this->tree = $tree_factory->getTreeById($skill_tree_id);
    }

    public function putIntoTree(\ilSkillTreeNode $node, int $parent_node_id, int $a_target_node_id = 0) : void
    {
        $tree = $this->tree;
        $node->setOrderNr($tree->getMaxOrderNr($parent_node_id) + 10);
        $node->update();

        // determine parent
        $parent_id = ($parent_node_id <= 0)
            ? $tree->readRootId()
            : $parent_node_id;

        // make a check, whether the type of object is allowed under
        // the parent
        $allowed = array(
            "skrt" => array("skll", "scat", "sktr", "sktp", "sctp"),
            "scat" => array("skll", "scat", "sktr"),
            "sctp" => array("sktp", "sctp"));
        $par_type = \ilSkillTreeNode::_lookupType($parent_id);
        if (!is_array($allowed[$par_type]) ||
            !in_array($node->getType(), $allowed[$par_type])) {
            return;
        }

        // determine target
        if ($a_target_node_id != 0) {
            $target = $a_target_node_id;
        } else {
            // determine last child that serves as predecessor
            $childs = $tree->getChilds($parent_id);

            if (count($childs) == 0) {
                $target = \ilTree::POS_FIRST_NODE;
            } else {
                $target = $childs[count($childs) - 1]["obj_id"];
            }
        }

        if ($tree->isInTree($parent_id) && !$tree->isInTree($node->getId())) {
            $tree->insertNode($node->getId(), $parent_id, $target);
        }
    }

    public function getWrittenPath(int $node_id, int $tref_id = 0) : string
    {
        $path = $this->tree->getSkillTreePath($node_id, $tref_id);

        $path_items = [];
        foreach ($path as $p) {
            if ($p["type"] != "skrt") {
                $path_items[] = $p["title"];
            }
        }
        return implode(" > ", $path_items);
    }

    public function getRootId() : int
    {
        return $this->tree->readRootId();
    }


    /**
     * Cut and copy a set of skills/skill categories into the clipboard
     */
    public function clipboardCut(array $a_ids) : void
    {
        $this->clearClipboard();
        $tree = $this->tree;

        $cut_ids = [];
        if (!is_array($a_ids)) {
            return;
        } else {
            // get all "top" ids, i.e. remove ids, that have a selected parent
            foreach ($a_ids as $id) {
                $path = $tree->getPathId($id);
                $take = true;
                foreach ($path as $path_id) {
                    if ($path_id != $id && in_array($path_id, $a_ids)) {
                        $take = false;
                    }
                }
                if ($take) {
                    $cut_ids[] = $id;
                }
            }
        }

        $this->clipboardCopy($cut_ids);

        // remove the objects from the tree
        // note: we are getting skills/categories which are *not* in the tree
        // we do not delete any pages/chapters here
        foreach ($cut_ids as $id) {
            $curnode = $tree->getNodeData($id);
            if ($tree->isInTree($id)) {
                $tree->deleteTree($curnode);
            }
        }
        // @todo check if needed
        \ilEditClipboard::setAction("cut");
    }

    /**
     * Copy a set of skills/skill categories into the clipboard
     */
    public function clipboardCopy(array $a_ids) : void
    {
        $ilUser = $this->user;

        $this->clearClipboard();
        $tree = $this->tree;

        // put them into the clipboard
        $time = date("Y-m-d H:i:s", time());
        $order = 0;
        foreach ($a_ids as $id) {
            $curnode = "";
            if ($tree->isInTree($id)) {
                $curnode = $tree->getNodeData($id);
                $subnodes = $tree->getSubTree($curnode);
                foreach ($subnodes as $subnode) {
                    if ($subnode["child"] != $id) {
                        $ilUser->addObjectToClipboard(
                            $subnode["child"],
                            $subnode["type"],
                            $subnode["title"],
                            $subnode["parent"],
                            $time,
                            $subnode["lft"]
                        );
                    }
                }
            }
            $order = ($curnode["lft"] > 0)
                ? $curnode["lft"]
                : (int) ($order + 1);
            $ilUser->addObjectToClipboard(
                $id,
                \ilSkillTreeNode::_lookupType($id),
                \ilSkillTreeNode::_lookupTitle($id),
                0,
                $time,
                $order
            );
        }
        \ilEditClipboard::setAction("copy");
    }


    /**
     * Insert basic skills from clipboard
     */
    public function insertItemsFromClip(string $a_type, int $a_obj_id) : array
    {
        $ilUser = $this->user;

        $parent_id = $a_obj_id;
        $target = \ilTree::POS_LAST_NODE;

        // cut and paste
        $skills = $ilUser->getClipboardObjects($a_type);  // this will get all skills _regardless_ of level
        $copied_nodes = [];
        foreach ($skills as $skill) {
            // if skill was already copied as part of tree - do not copy it again
            if (!in_array($skill["id"], array_keys($copied_nodes))) {
                $cid = $this->pasteTree(
                    (int) $skill["id"],
                    $parent_id,
                    $target,
                    $skill["insert_time"],
                    $copied_nodes,
                    (\ilEditClipboard::getAction() == "copy"),
                    true
                );
                //				$target = $cid;
            }
        }

        $this->clearClipboard();

        $this->saveChildsOrder(
            $a_obj_id,
            [],
            in_array($a_type, array("sktp", "sctp"))
        );

        return $copied_nodes;
    }

    /**
     * Remove all skill items from clipboard
     */
    public static function clearClipboard() : void
    {
        global $DIC;

        $ilUser = $DIC->user();

        $ilUser->clipboardDeleteObjectsOfType("skll");
        $ilUser->clipboardDeleteObjectsOfType("scat");
        $ilUser->clipboardDeleteObjectsOfType("sktr");
        $ilUser->clipboardDeleteObjectsOfType("sktp");
        $ilUser->clipboardDeleteObjectsOfType("sctp");
        \ilEditClipboard::clear();
    }


    /**
     * Paste item (tree) from clipboard to skill tree
     */
    protected function pasteTree(
        int $a_item_id,
        int $a_parent_id,
        int $a_target,
        string $a_insert_time,
        array &$a_copied_nodes,
        bool $a_as_copy = false,
        bool $a_add_suffix = false
    ) : int {
        global $DIC;

        $ilUser = $this->user;
        $ilLog = $DIC["ilLog"];
        $lng = $DIC->language();

        $item_type = \ilSkillTreeNode::_lookupType($a_item_id);

        $item = null;
        if ($item_type == "scat") {
            $item = new \ilSkillCategory($a_item_id);
        } elseif ($item_type == "skll") {
            $item = new \ilBasicSkill($a_item_id);
        } elseif ($item_type == "sktr") {
            $item = new \ilSkillTemplateReference($a_item_id);
        } elseif ($item_type == "sktp") {
            $item = new \ilBasicSkillTemplate($a_item_id);
        } elseif ($item_type == "sctp") {
            $item = new \ilSkillTemplateCategory($a_item_id);
        }

        $ilLog->write("Getting from clipboard type " . $item_type . ", " .
            "Item ID: " . $a_item_id);

        if ($a_as_copy) {
            $target_item = $item->copy();
            if ($a_add_suffix) {
                $target_item->setTitle($target_item->getTitle() . " " . $lng->txt("copy_of_suffix"));
                $target_item->update();
            }
            $a_copied_nodes[$item->getId()] = $target_item->getId();
        } else {
            $target_item = $item;
        }

        $ilLog->write("Putting into skill tree type " . $target_item->getType() .
            "Item ID: " . $target_item->getId() . ", Parent: " . $a_parent_id . ", " .
            "Target: " . $a_target);

        $this->putIntoTree($target_item, $a_parent_id, $a_target);

        $childs = $ilUser->getClipboardChilds($item->getId(), $a_insert_time);

        foreach ($childs as $child) {
            $this->pasteTree(
                (int) $child["id"],
                $target_item->getId(),
                \ilTree::POS_LAST_NODE,
                $a_insert_time,
                $a_copied_nodes,
                $a_as_copy
            );
        }

        return $target_item->getId();
    }

    public function saveChildsOrder(int $a_par_id, array $a_childs_order, bool $a_templates = false) : void
    {
        $skill_tree = $this->tree;

        if ($a_par_id != $skill_tree->readRootId()) {
            $childs = $skill_tree->getChilds($a_par_id);
        } else {
            if ($a_templates) {
                $childs = $skill_tree->getChildsByTypeFilter(
                    $a_par_id,
                    array("skrt", "sktp", "sctp")
                );
            } else {
                $childs = $skill_tree->getChildsByTypeFilter(
                    $a_par_id,
                    array("skrt", "skll", "scat", "sktr")
                );
            }
        }

        foreach ($childs as $k => $c) {
            if (isset($a_childs_order[$c["child"]])) {
                $childs[$k]["order_nr"] = (int) $a_childs_order[$c["child"]];
            }
        }

        $childs = ilArrayUtil::sortArray($childs, "order_nr", "asc", true);

        $cnt = 10;
        foreach ($childs as $c) {
            \ilSkillTreeNode::_writeOrderNr($c["child"], $cnt);
            $cnt += 10;
        }
    }

    /**
     * Get top skill templates and template categories
     */
    public function getTopTemplates() : array
    {
        return $this->tree->getChildsByTypeFilter(
            $this->tree->readRootId(),
            array("sktp", "sctp")
        );
    }
}
