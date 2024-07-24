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

namespace Skills;

use ilDBInterface;
use ILIAS\Test\Skills\TestSkillDBRepository;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;

class TestSkillDBRepositoryTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $testSkillDBRepository = new TestSkillDBRepository();
        $this->assertInstanceOf(TestSkillDBRepository::class, $testSkillDBRepository);

        $testSkillDBRepository = new TestSkillDBRepository($this->createMock(ilDBInterface::class));
        $this->assertInstanceOf(TestSkillDBRepository::class, $testSkillDBRepository);
    }

    /**
     * @dataProvider removeForSkillDataProvider
     * @throws Exception
     */
    public function testRemoveForSkill(array $input, string $output): void
    {
        $skill_node_id_quoted = '`' . $input['skill_node_id'] .'`';

        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->once())->method('manipulate')->with(
            $this->equalTo('DELETE FROM tst_skl_thresholds  WHERE ' . $output . ' = ' . $skill_node_id_quoted)
        );
        $db->expects($this->once())->method('quote')->with(
            $this->equalTo($input['skill_node_id']),
            $this->equalTo('integer')
        )->willReturn($skill_node_id_quoted);

        $this->assertNull((new TestSkillDBRepository($db))->removeForSkill($input['skill_node_id'], $input['is_reference']));
    }

    public static function removeForSkillDataProvider(): array
    {
        return [
            'not_reference_0' => [
                'input' => [
                    'skill_node_id' => 0,
                    'is_reference' => false
                ],
                'output' => 'skill_base_fi'
            ],
            'reference_0' => [
                'input' => [
                    'skill_node_id' => 0,
                    'is_reference' => true
                ],
                'output' => 'skill_tref_fi'
            ],
            'not_reference_1' => [
                'input' => [
                    'skill_node_id' => 1,
                    'is_reference' => false
                ],
                'output' => 'skill_base_fi'
            ],
            'reference_1' => [
                'input' => [
                    'skill_node_id' => 1,
                    'is_reference' => true
                ],
                'output' => 'skill_tref_fi'
            ],
            'not_reference_2' => [
                'input' => [
                    'skill_node_id' => 2,
                    'is_reference' => false
                ],
                'output' => 'skill_base_fi'
            ],
            'reference_2' => [
                'input' => [
                    'skill_node_id' => 2,
                    'is_reference' => true
                ],
                'output' => 'skill_tref_fi'
            ]
        ];
    }
}
