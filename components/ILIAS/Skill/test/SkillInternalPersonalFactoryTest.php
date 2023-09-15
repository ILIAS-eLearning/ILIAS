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
use ILIAS\Skill\Personal;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillInternalPersonalFactoryTest extends TestCase
{
    protected Personal\PersonalSkillFactory $factory;
    protected Personal\SelectedUserSkill $selected_skill;
    protected Personal\AssignedMaterial $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Personal\PersonalSkillFactory();

        $this->selected_skill = $this->factory->selectedUserSkill(0, "");
        $this->material = $this->factory->assignedMaterial(0, 0, 0, 0, 0, 0);
    }

    public function testFactoryInstances(): void
    {
        $this->assertInstanceOf(Personal\SelectedUserSkill::class, $this->selected_skill);
        $this->assertInstanceOf(Personal\AssignedMaterial::class, $this->material);
    }
}
