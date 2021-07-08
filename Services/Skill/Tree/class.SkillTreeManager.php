<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Skill\Tree;

/**
 * Skill tree manager
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillTreeManager
{
    /**
     * @var int
     */
    protected $skmg_ref_id;

    /**
     * @var \ilTree
     */
    protected $repository_tree;

    /**
     * @var SkillTreeFactory
     */
    protected $tree_factory;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     * @param int     $skmg_ref_id
     * @param \ilTree $repository_tree
     */
    public function __construct(
        int $skmg_ref_id, \ilTree $repository_tree, SkillTreeFactory $tree_factory)
    {
        global $DIC;

        $this->skmg_ref_id = $skmg_ref_id;
        $this->repository_tree = $repository_tree;
        $this->tree_factory = $tree_factory;
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * Create new tree object
     * @param string $title
     * @param string $description
     */
    public function createTree(string $title, string $description) : void
    {
        $tree_obj = new \ilObjSkillTree();
        $tree_obj->setTitle($title);
        $tree_obj->setDescription($description);
        $tree_obj->create();
        $tree_obj->createReference();
        $tree_obj->putInTree($this->skmg_ref_id);
        $tree_obj->setPermissions($this->skmg_ref_id);

        $tree = $this->tree_factory->getById($tree_obj->getId());
        $root_node = new \ilSkillRoot();
        $root_node->setTitle("Skill Tree Root Node");
        $root_node->create();
        $tree->addTree($tree_obj->getId(), $root_node->getId());
        $this->ctrl->setParameterByClass("ilobjskilltreegui", "ref_id", $tree_obj->getRefId());
        $this->ctrl->setParameterByClass("ilobjskilltreegui", "obj_id", $tree->readRootId());
    }

    /**
     * Create new tree object
     * @param \ilObjSkillTree $tree_obj
     * @param string          $title
     * @param string          $description
     */
    public function updateTree(\ilObjSkillTree $tree_obj, string $title, string $description) : void
    {
        $tree_obj->setTitle($title);
        $tree_obj->setDescription($description);
        $tree_obj->update();
    }

    public function deleteTree(\ilObjSkillTree $tree_obj) : void
    {
        $tree_obj->delete();
    }

    /**
     * Get tree objects
     * @return \Generator
     */
    public function getTrees() : \Generator
    {
        foreach ($this->repository_tree->getChilds($this->skmg_ref_id) as $c) {
            yield new \ilObjSkillTree($c["child"]);
        }
    }

    /**
     * Get tree object
     * @return \ilObjSkillTree
     */
    public function getTree(int $skl_tree_id) : \ilObjSkillTree
    {
        $skl_tree_id = (int) current(\ilObject::_getAllReferences($skl_tree_id));
        return new \ilObjSkillTree($skl_tree_id);
    }

    /**
     * Get ref id of skill management administration node
     * @return int
     */
    public function getSkillManagementRefId() : int
    {
        return $this->skmg_ref_id;
    }

}