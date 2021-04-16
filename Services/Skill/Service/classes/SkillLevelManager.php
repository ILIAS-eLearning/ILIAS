<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill level manager
 * @author famula@leifos.de
 */
class SkillLevelManager
{
    /**
     * @var SkillInternalRepoService
     */
    protected $repo_service;

    /**
     * Constructor
     */
    public function __construct(SkillInternalRepoService $repo_service = null)
    {
        global $DIC;

        $this->repo_service = ($repo_service)
            ?: $DIC->skills()->internal()->repo();
    }
}