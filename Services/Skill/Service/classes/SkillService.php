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

namespace ILIAS\Skill\Service;

/**
 * Skill service
 * @author famula@leifos.de
 */
class SkillService implements SkillServiceInterface
{
    public function __construct()
    {
    }

    public function user(int $id) : SkillUserService
    {
        return new SkillUserService($id);
    }

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