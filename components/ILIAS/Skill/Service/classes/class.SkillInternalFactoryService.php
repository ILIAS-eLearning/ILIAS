<?php

declare(strict_types=1);

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

use ILIAS\Skill\Tree;
use ILIAS\Skill\Profile;
use ILIAS\Skill\Personal;
use ILIAS\Skill\Resource;

/**
 * Skill internal factory service
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillInternalFactoryService
{
    public function tree(): Tree\SkillTreeFactory
    {
        return new Tree\SkillTreeFactory();
    }

    public function profile(): Profile\SkillProfileFactory
    {
        return new Profile\SkillProfileFactory();
    }

    public function personal(): Personal\PersonalSkillFactory
    {
        return new Personal\PersonalSkillFactory();
    }

    public function resource(): Resource\SkillResourceFactory
    {
        return new Resource\SkillResourceFactory();
    }
}
