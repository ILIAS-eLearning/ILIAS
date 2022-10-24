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

/**
 * Class ilAssQuestionSkillAssignmentRegistryTest
 */
class ilAssQuestionSkillAssignmentRegistryTest extends assBaseTestCase
{
    public const TEST_KEY = 'phpunit_tst';

    /**
     * @var array
     */
    protected $storage = array();

    /**
     *
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->storage = array();
    }

    /**
     * @dataProvider serializedData
     * @param          $value
     * @param          $chunkSize
     * @param callable $preCallback
     * @param callable $postCallback
     */
    public function testSkillAssignmentsCanBetStoredAndFetchedBySerializationStrategy($value, $chunkSize, callable $preCallback, callable $postCallback): void
    {
        require_once 'Services/Administration/classes/class.ilSetting.php';
        $settingsMock = $this->getMockBuilder('ilSetting')->disableOriginalConstructor()->onlyMethods(array('set', 'get', 'delete'))->getMock();

        $settingsMock->expects($this->any())->method('set')->will(
            $this->returnCallback(function ($key, $value) {
                $this->storage[$key] = $value;
            })
        );

        $settingsMock->expects($this->any())->method('get')->will(
            $this->returnCallback(function ($key, $value) {
                return $this->storage[$key] ?? $value;
            })
        );

        $settingsMock->expects($this->any())->method('delete')->will(
            $this->returnCallback(function ($key, $value) {
                if (isset($this->storage[$key])) {
                    unset($this->storage[$key]);
                }
            })
        );

        $valueToTest = $preCallback($value);

        $registry = new \ilAssQuestionSkillAssignmentRegistry($settingsMock);
        $registry->setChunkSize($chunkSize);
        $registry->setStringifiedImports(self::TEST_KEY, $valueToTest);
        $actual = $registry->getStringifiedImports(self::TEST_KEY);

        $this->assertEquals($valueToTest, $actual);
        $this->assertEquals($value, $postCallback($actual));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testInvalidChunkSizeWillRaiseException(): void
    {
        require_once 'Services/Administration/classes/class.ilSetting.php';
        $settingsMock = $this->getMockBuilder('ilSetting')->disableOriginalConstructor()->onlyMethods(array('set', 'get', 'delete'))->getMock();

        try {
            $registry = new \ilAssQuestionSkillAssignmentRegistry($settingsMock);
            $registry->setChunkSize("a");
            $this->fail("Failed asserting that exception of type \"InvalidArgumentException\" is thrown.");
        } catch (\InvalidArgumentException $e) {
        }

        try {
            $registry = new \ilAssQuestionSkillAssignmentRegistry($settingsMock);
            $registry->setChunkSize(-5);
            $this->fail("Failed asserting that exception of type \"InvalidArgumentException\" is thrown.");
        } catch (\InvalidArgumentException $e) {
        }
    }

    /**
     * @param callable $pre
     * @param callable $post
     * @return array
     */
    protected function getTestData(callable $pre, callable $post): array
    {
        $data = [];

        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportList.php';
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImport.php';
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';
        $assignmentList = new \ilAssQuestionSkillAssignmentImportList();

        for ($i = 0; $i < 5; $i++) {
            $assignment = new \ilAssQuestionSkillAssignmentImport();
            $assignment->setEvalMode(\ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION);
            $assignment->setImportSkillTitle('phpunit' . $i);
            $assignment->setImportSkillPath('phpunit' . $i);
            $random = new \ilRandom();
            $assignment->setSkillPoints($random->int(0, PHP_INT_MAX));
            $assignment->setImportQuestionId($random->int(0, PHP_INT_MAX));
            $assignment->setImportSkillBaseId($random->int(0, PHP_INT_MAX));
            $assignment->setImportSkillTrefId($random->int(0, PHP_INT_MAX));

            $assignmentList->addAssignment($assignment);
        }

        $rawData = array(
            array("This is a Test", 2),
            array(array("üäöÖÜÄÖß"), 2),
            array("This is a Test with a huge chunk size", 10000),
            array($assignmentList, 7)
        );

        foreach ($rawData as $rawItem) {
            $data[] = array(
                $rawItem[0], $rawItem[1], $pre, $post
            );
        }

        return $data;
    }

    /**
     * @return array
     */
    public function serializedData(): array
    {
        $pre = function ($value) {
            return \serialize($value);
        };

        $post = function ($value) {
            return \unserialize($value);
        };

        return $this->getTestData($pre, $post);
    }
}
