<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill service interface
 * @author famula@leifos.de
 */
interface SkillServiceInterface
{
    /**
     * Internal service, do not use in other components
     * @return SkillInternalService
     */
    public function internal() : SkillInternalService;
}
