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
class SkillPersonalTest extends TestCase
{
    protected Personal\SelectedUserSkill $selected_skill;
    protected Personal\AssignedMaterial $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->selected_skill = new Personal\SelectedUserSkill(
            11,
            "My selected skill"
        );
        $this->material = new Personal\AssignedMaterial(
            21,
            22,
            23,
            24,
            25,
            26
        );
    }

    public function testSelectedSkillProperties(): void
    {
        $s = $this->selected_skill;

        $this->assertEquals(
            11,
            $s->getSkillNodeId()
        );
        $this->assertEquals(
            "My selected skill",
            $s->getTitle()
        );
    }

    public function testMaterialProperties(): void
    {
        $m = $this->material;

        $this->assertEquals(
            21,
            $m->getUserId()
        );
        $this->assertEquals(
            22,
            $m->getTopSkillId()
        );
        $this->assertEquals(
            23,
            $m->getSkillId()
        );
        $this->assertEquals(
            24,
            $m->getLevelId()
        );
        $this->assertEquals(
            25,
            $m->getWorkspaceId()
        );
        $this->assertEquals(
            26,
            $m->getTrefId()
        );
    }
}
