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
     * Constructor
     * @param int     $skmg_ref_id
     * @param \ilTree $repository_tree
     */
    public function __construct(
        int $skmg_ref_id, \ilTree $repository_tree)
    {
        $this->skmg_ref_id = $skmg_ref_id;
        $this->repository_tree = $repository_tree;
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
     * Get ref id of skill management administration node
     * @return int
     */
    public function getSkillManagementRefId() : int
    {
        return $this->skmg_ref_id;
    }

}