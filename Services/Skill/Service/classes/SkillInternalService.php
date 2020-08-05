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
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Skill service repos
     * @return SkillInternalRepoService
     */
    public function repo()
    {
        return new SkillInternalRepoService();
    }

    public function manager()
    {
        return new SkillInternalManagerService();
    }
}