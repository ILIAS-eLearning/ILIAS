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
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class TestSkillDBRepositoryTest extends ilTestBaseTestCase
{
    private TestSkillDBRepository $testSkillDBRepository;

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSkillDBRepository = $this->createInstanceOf(TestSkillDBRepository::class);
    }

    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(TestSkillDBRepository::class, $this->testSkillDBRepository);

        $testSkillDBRepository = new TestSkillDBRepository($this->createMock(ilDBInterface::class));
        $this->assertInstanceOf(TestSkillDBRepository::class, $testSkillDBRepository);
    }

    /**
     * @dataProvider removeForSkillDataProvider
     * @throws \Exception
     */
    public function testRemoveForSkill(array $input, string $output): void
    {
        $skill_node_id_quoted = '`' . $input['skill_node_id'] . '`';

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($input, $output, $skill_node_id_quoted) {
            $mock
                ->expects($this->once())
                ->method('manipulate')
                ->with($this->equalTo('DELETE FROM tst_skl_thresholds  WHERE ' . $output . ' = ' . $skill_node_id_quoted));

            $mock
                ->expects($this->once())
                ->method('quote')
                ->with($this->equalTo($input['skill_node_id']), $this->equalTo('integer'))
                ->willReturn($skill_node_id_quoted);
        });

        $this->testSkillDBRepository->removeForSkill($input['skill_node_id'], $input['is_reference']);
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
