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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Skill\Personal;

use PHPUnit\Framework\TestCase;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillInternalPersonalFactoryTest extends TestCase
{
    protected PersonalSkillFactory $factory;
    protected SelectedUserSkill $selected_skill;
    protected AssignedMaterial $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new PersonalSkillFactory();

        $this->selected_skill = $this->factory->selectedUserSkill(0, "");
        $this->material = $this->factory->assignedMaterial(0, 0, 0, 0, 0, 0);
    }

    public function testFactoryInstances(): void
    {
        $this->assertInstanceOf(SelectedUserSkill::class, $this->selected_skill);
        $this->assertInstanceOf(AssignedMaterial::class, $this->material);
    }
}
