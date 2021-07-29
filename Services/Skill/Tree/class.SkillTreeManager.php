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
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilErrorHandling
     */
    protected $error;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $requested_ref_id;

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
     * @var \ILIAS\Skill\Access\SkillTreeAccess
     */
    protected $tree_access_manager;

    /**
     * @var \ILIAS\Skill\Access\SkillManagementAccess
     */
    protected $management_access_manager;

    /**
     * Constructor
     * @param int     $skmg_ref_id
     * @param \ilTree $repository_tree
     */
    public function __construct(
        int $skmg_ref_id, \ilTree $repository_tree, SkillTreeFactory $tree_factory)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->error = $DIC["ilErr"];
        $this->lng = $DIC->language();
        $this->request = $DIC->http()->request();
        $this->skmg_ref_id = $skmg_ref_id;
        $this->repository_tree = $repository_tree;
        $this->tree_factory = $tree_factory;

        $params = $this->request->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? 0);
        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($this->requested_ref_id);
        $this->management_access_manager = $DIC->skills()->internal()->manager()->getManagementAccessManager($this->requested_ref_id);
    }

    /**
     * Create new tree object
     * @param string $title
     * @param string $description
     */
    public function createTree(string $title, string $description) : void
    {
        if (!$this->management_access_manager->hasCreateTreePermission()) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }
        $tree_obj = new \ilObjSkillTree();
        $tree_obj->setTitle($title);
        $tree_obj->setDescription($description);
        $tree_obj->create();
        $tree_obj->createReference();
        $tree_obj->putInTree($this->skmg_ref_id);
        $tree_obj->setPermissions($this->skmg_ref_id);

        $tree = $this->tree_factory->getTreeById($tree_obj->getId());
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
        if (!$this->tree_access_manager->hasEditTreeSettingsPermission()) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }
        $tree_obj->setTitle($title);
        $tree_obj->setDescription($description);
        $tree_obj->update();
    }

    public function deleteTree(\ilObjSkillTree $tree_obj) : void
    {
        if (!$this->management_access_manager->hasCreateTreePermission()) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }
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
     *
     * @param int $tree_id
     * @return \ilObjSkillTree
     */
    public function getTree(int $tree_id) : \ilObjSkillTree
    {
        $ref_id = (int) current(\ilObject::_getAllReferences($tree_id));
        return new \ilObjSkillTree($ref_id);
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