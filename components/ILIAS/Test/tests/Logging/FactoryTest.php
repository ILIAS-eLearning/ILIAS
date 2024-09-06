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

namespace Logging;

use ILIAS\Test\Logging\Factory;
use ILIAS\Test\Logging\TestAdministrationInteraction;
use ILIAS\Test\Logging\TestAdministrationInteractionTypes;
use ILIAS\Test\Logging\TestError;
use ILIAS\Test\Logging\TestErrorTypes;
use ILIAS\Test\Logging\TestParticipantInteraction;
use ILIAS\Test\Logging\TestParticipantInteractionTypes;
use ILIAS\Test\Logging\TestQuestionAdministrationInteraction;
use ILIAS\Test\Logging\TestQuestionAdministrationInteractionTypes;
use ILIAS\Test\Logging\TestScoringInteraction;
use ILIAS\Test\Logging\TestScoringInteractionTypes;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;
use stdClass;

class FactoryTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Factory::class, $this->createInstanceOf(Factory::class));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testBuildTestAdministrationInteraction(): void
    {
        $factory = $this->createInstanceOf(Factory::class);
        $this->assertInstanceOf(
            TestAdministrationInteraction::class,
            $factory->buildTestAdministrationInteraction(
                1,
                2,
                TestAdministrationInteractionTypes::EXTRA_TIME_ADDED,
                []
            )
        );
    }

    /**
     * @dataProvider buildTestAdministrationInteractionFromDBValuesDataProvider
     * @throws Exception|ReflectionException
     */
    public function testBuildTestAdministrationInteractionFromDBValues(string $type): void
    {
        $factory = $this->createInstanceOf(Factory::class);

        $db_values = $this->createMock(stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->id = 1;
        $db_values->additional_data = '';
        $db_values->ref_id = 1;
        $db_values->admin_id = 1;
        $db_values->modification_ts = 1;

        if ($type === 'new_test_created') {
            $this->assertInstanceOf(TestAdministrationInteraction::class, $factory->buildTestAdministrationInteractionFromDBValues($db_values));
            return;
        }

        $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
        $factory->buildTestAdministrationInteractionFromDBValues($db_values);
    }

    public static function buildTestAdministrationInteractionFromDBValuesDataProvider(): array
    {
        return [
            'valid_type' => ['new_test_created'],
            'invalid_type' => ['invalid_type']
        ];
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testBuildTestQuestionAdministrationInteraction(): void
    {
        $factory = $this->createInstanceOf(Factory::class);
        $this->assertInstanceOf(
            TestQuestionAdministrationInteraction::class,
            $factory->buildTestQuestionAdministrationInteraction(
                1,
                2,
                3,
                TestQuestionAdministrationInteractionTypes::QUESTION_MODIFIED,
                []
            )
         );
    }

    /**
     * @dataProvider buildQuestionAdministrationInteractionFromDBValuesDataProvider
     * @throws Exception|ReflectionException
     */
    public function testBuildQuestionAdministrationInteractionFromDBValues(string $type): void
    {
        $factory = $this->createInstanceOf(Factory::class);

        $db_values = $this->createMock(stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->qst_id = 1;
        $db_values->additional_data = '';
        $db_values->ref_id = 1;
        $db_values->admin_id = 1;
        $db_values->modification_ts = 1;
        $db_values->id = 1;

        if ($type === 'question_modified') {
            $this->assertInstanceOf(
                TestQuestionAdministrationInteraction::class,
                $factory->buildQuestionAdministrationInteractionFromDBValues($db_values)
            );
            return;
        }

        $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
        $factory->buildQuestionAdministrationInteractionFromDBValues($db_values);
    }

    public static function buildQuestionAdministrationInteractionFromDBValuesDataProvider(): array
    {
        return [
            'valid_type' => ['question_modified'],
            'invalid_type' => ['invalid_type']
        ];
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testBuildParticipantInteraction(): void
    {
        $factory = $this->createInstanceOf(Factory::class);
        $this->assertInstanceOf(
            TestParticipantInteraction::class,
            $factory->buildParticipantInteraction(
                1,
                2,
                3,
                'address',
                TestParticipantInteractionTypes::TEST_RUN_FINISHED,
                []
            )
        );
    }

    /**
     * @dataProvider buildParticipantInteractionFromDBValuesDataProvider
     * @throws Exception|ReflectionException
     */
    public function testBuildParticipantInteractionFromDBValues(string $type): void
    {
        $factory = $this->createInstanceOf(Factory::class);

        $db_values = $this->createMock(stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->qst_id = 1;
        $db_values->additional_data = '';
        $db_values->ref_id = 1;
        $db_values->pax_id = 1;
        $db_values->source_ip = 'ip';
        $db_values->modification_ts = 1;
        $db_values->id = 1;

        if ($type === 'test_run_started') {
            $this->assertInstanceOf(TestParticipantInteraction::class, $factory->buildParticipantInteractionFromDBValues($db_values));
            return;
        }

        $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
        $factory->buildParticipantInteractionFromDBValues($db_values);
    }

    public static function buildParticipantInteractionFromDBValuesDataProvider(): array
    {
        return [
            'valid_type' => ['test_run_started'],
            'invalid_type' => ['invalid_type']
        ];
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testBuildScoringInteraction(): void
    {
        $factory = $this->createInstanceOf(Factory::class);
        $this->assertInstanceOf(
            TestScoringInteraction::class,
            $factory->buildScoringInteraction(
                1,
                2,
                3,
                4,
                TestScoringInteractionTypes::QUESTION_GRADED,
                []
            )
        );
    }

    /**
     * @dataProvider provideScoringInteractionType
     * @throws Exception|ReflectionException
     */
    public function test_buildScoringInteractionFromDBValues(string $type): void
    {
        $factory = $this->createInstanceOf(Factory::class);

        $db_values = $this->createMock(stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->qst_id = 1;
        $db_values->additional_data = '';
        $db_values->ref_id = 1;
        $db_values->pax_id = 1;
        $db_values->admin_id = 1;
        $db_values->modification_ts = 1;
        $db_values->id = 1;

        if ($type === 'question_graded') {
            $this->assertInstanceOf(TestScoringInteraction::class, $factory->buildScoringInteractionFromDBValues($db_values));
            return;
        }

        $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
        $factory->buildScoringInteractionFromDBValues($db_values);
    }

    public static function provideScoringInteractionType(): array
    {
        return [
            'valid_type' => ['question_graded'],
            'invalid_type' => ['invalid_type']
        ];
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testBuildError(): void
    {
        $factory = $this->createInstanceOf(Factory::class);
        $this->assertInstanceOf(
            TestError::class,
            $factory->buildError(
                1,
                2,
                3,
                4,
                TestErrorTypes::ERROR_ON_PARTICIPANT_INTERACTION,
                'message'
            )
        );
    }

    /**
     * @dataProvider buildErrorFromDBValuesDataProvider
     * @throws Exception|ReflectionException
     */
    public function testBuildErrorFromDBValues($type): void
    {
        $factory = $this->createInstanceOf(Factory::class);

        $db_values = $this->createMock(stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->qst_id = 1;
        $db_values->error_message = 'test';
        $db_values->ref_id = 1;
        $db_values->pax_id = 1;
        $db_values->admin_id = 1;
        $db_values->modification_ts = 1;
        $db_values->id = 1;

        if ($type === 'error_on_participant_interaction') {
            $this->assertInstanceOf(TestError::class, $factory->buildErrorFromDBValues($db_values));
            return;
        }

        $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
        $factory->buildErrorFromDBValues($db_values);
    }

    public static function buildErrorFromDBValuesDataProvider(): array
    {
        return [
            'valid_type' => ['error_on_participant_interaction'],
            'invalid_type' => ['invalid_type']
        ];
    }
}
