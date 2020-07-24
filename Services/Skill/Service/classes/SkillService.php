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
     * Constructor
     */
    public function __construct()
    {
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

    /**
     * @inheritDoc
     */
    public function internal() : SkillInternalService
    {
        return new SkillInternalService();
    }
}