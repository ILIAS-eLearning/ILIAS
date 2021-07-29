<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill service
 * @author famula@leifos.de
 */
class SkillService implements SkillServiceInterface
{
    /**
     * @var int ref id of skill management administration node
     */
    protected $skmg_ref_id;

    /**
     * @var \ilTree
     */
    protected $repository_tree;

    /**
     * @var \ilRbacSystem
     */
    protected $rbac_system;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->repository_tree = $DIC->repositoryTree();
        $skmg_obj = current(\ilObject::_getObjectsByType("skmg"));
        $this->skmg_ref_id = (int) current(\ilObject::_getAllReferences($skmg_obj["obj_id"]));
        $this->rbac_system = $DIC->rbac()->system();
        $this->usr_id = $DIC->user()->getId();
    }

    /**
     * @param int $id
     * @return SkillUserService
     */
    public function user(int $id) : SkillUserService
    {
        return new SkillUserService($id);
    }

    /**
     * @return SkillUIService
     */
    public function ui() : SkillUIService
    {
        return new SkillUIService();
    }

    public function tree() : SkillTreeService
    {
        return new SkillTreeService($this->internal());
    }

    /**
     * @inheritDoc
     */
    public function internal() : SkillInternalService
    {
        return new SkillInternalService(
            $this->skmg_ref_id,
            $this->repository_tree,
            $this->rbac_system,
            $this->usr_id
        );
    }
}