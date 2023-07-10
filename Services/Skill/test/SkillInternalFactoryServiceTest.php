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

use PHPUnit\Framework\TestCase;
use ILIAS\Skill\Service;
use ILIAS\Skill\Profile;
use ILIAS\Skill\Personal;
use ILIAS\Skill\Tree;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillInternalFactoryServiceTest extends TestCase
{
    protected Service\SkillInternalFactoryService $factory;
    protected Profile\SkillProfileFactory $profile_fac;
    protected Personal\PersonalSkillFactory $personal_fac;
    protected Tree\SkillTreeFactory $tree_fac;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Service\SkillInternalFactoryService();

        $this->profile_fac = $this->factory->profile();
        $this->personal_fac = $this->factory->personal();
        $this->tree_fac = $this->factory->tree();
    }

    public function testFactoryInstances(): void
    {
        $this->assertInstanceOf(Profile\SkillProfileFactory::class, $this->profile_fac);
        $this->assertInstanceOf(Personal\PersonalSkillFactory::class, $this->personal_fac);
        $this->assertInstanceOf(Tree\SkillTreeFactory::class, $this->tree_fac);
    }
}
