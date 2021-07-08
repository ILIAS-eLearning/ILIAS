<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill internal service
 * @author famula@leifos.de
 */
class SkillInternalService
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
    public function __construct(int $skmg_ref_id, \ilTree $repository_tree, \ilRbacSystem $rbac_system, int $usr_id)
    {
        $this->skmg_ref_id = $skmg_ref_id;
        $this->repository_tree = $repository_tree;
        $this->rbac_system = $rbac_system;
        $this->usr_id = $usr_id;
    }

    /**
     * Skill service repos
     * @return SkillInternalRepoService
     */
    public function repo()
    {
        return new SkillInternalRepoService($this->factory());
    }

    public function manager()
    {
        return new SkillInternalManagerService(
            $this->skmg_ref_id,
            $this->repository_tree,
            $this->factory()->tree(),
            $this->rbac_system,
            $this->usr_id
        );
    }

    /**
     * Skill service repos
     * @return SkillInternalFactoryService
     */
    public function factory()
    {
        return new SkillInternalFactoryService();
    }

}