<?php

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
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    private Factory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new Factory();
    }
    public function test_buildTestAdministrationInteraction(): void
    {
        $this->assertInstanceOf(TestAdministrationInteraction::class, $this->testObj->buildTestAdministrationInteraction(1, 2, TestAdministrationInteractionTypes::EXTRA_TIME_ADDED, []));
    }

    /**
     * @dataProvider provideInteractionType
     */
    public function test_buildTestAdministrationInteractionFromDBValues($type): void
    {
        $db_values = $this->createMock(\stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->id = 1;
        $db_values->additional_data = "";
        $db_values->ref_id = 1;
        $db_values->admin_id = 1;
        $db_values->modification_ts = 1;
        if(TestAdministrationInteractionTypes::tryFrom($type) !== null) {
            $this->assertInstanceOf(TestAdministrationInteraction::class, $this->testObj->buildTestAdministrationInteractionFromDBValues($db_values));
        } else {
            $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
            $this->testObj->buildTestAdministrationInteractionFromDBValues($db_values);
        }
    }

    public static function provideInteractionType(): array
    {
        return [
            "dataset 1: valid type" => [
                "type" => "new_test_created"
            ],
            "dataset 2: invalid type" => [
                "type" => "type"
            ]
        ];
    }

    public function test_buildTestQuestionAdministrationInteraction(): void
    {
        $this->assertInstanceOf(TestQuestionAdministrationInteraction::class, $this->testObj->buildTestQuestionAdministrationInteraction(1, 2, 3, TestQuestionAdministrationInteractionTypes::QUESTION_MODIFIED, []));
    }

    /**
     * @dataProvider provideQuestionInteractionType
     */
    public function test_buildQuestionAdministrationInteractionFromDBValues($type): void
    {
        $db_values = $this->createMock(\stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->qst_id = 1;
        $db_values->additional_data = "";
        $db_values->ref_id = 1;
        $db_values->admin_id = 1;
        $db_values->modification_ts = 1;
        $db_values->id = 1;
        if(TestQuestionAdministrationInteractionTypes::tryFrom($type) !== null) {
            $this->assertInstanceOf(TestQuestionAdministrationInteraction::class, $this->testObj->buildQuestionAdministrationInteractionFromDBValues($db_values));
        } else {
            $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
            $this->testObj->buildQuestionAdministrationInteractionFromDBValues($db_values);
        }
    }

    public static function provideQuestionInteractionType(): array
    {
        return [
            "dataset 1: valid type" => [
                "type" => "question_modified"
            ],
            "dataset 2: invalid type" => [
                "type" => "type"
            ]
        ];
    }

    public function test_buildParticipantInteraction(): void
    {
        $this->assertInstanceOf(TestParticipantInteraction::class, $this->testObj->buildParticipantInteraction(1, 2, 3, "address", TestParticipantInteractionTypes::TEST_RUN_FINISHED, []));
    }

    /**
     * @dataProvider provideParticipantInteractionType
     */
    public function test_buildParticipantInteractionFromDBValues($type): void
    {
        $db_values = $this->createMock(\stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->qst_id = 1;
        $db_values->additional_data = "";
        $db_values->ref_id = 1;
        $db_values->pax_id = 1;
        $db_values->source_ip = "ip";
        $db_values->modification_ts = 1;
        $db_values->id = 1;
        if (TestParticipantInteractionTypes::tryFrom($type) !== null) {
            $this->assertInstanceOf(TestParticipantInteraction::class, $this->testObj->buildParticipantInteractionFromDBValues($db_values));
        } else {
            $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
            $this->testObj->buildParticipantInteractionFromDBValues($db_values);
        }

    }

    public static function provideParticipantInteractionType(): array
    {
        return [
            "dataset 1: valid type" => [
                "type" => "test_run_started"
            ],
            "dataset 2: invalid type" => [
                "type" => "type"
            ]
        ];
    }

    public function test_buildScoringInteraction(): void
    {
        $this->assertInstanceOf(TestScoringInteraction::class, $this->testObj->buildScoringInteraction(1, 2, 3, 4, TestScoringInteractionTypes::QUESTION_GRADED, []));
    }

    /**
     * @dataProvider provideScoringInteractionType
     */
    public function test_buildScoringInteractionFromDBValues($type): void
    {
        $db_values = $this->createMock(\stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->qst_id = 1;
        $db_values->additional_data = "";
        $db_values->ref_id = 1;
        $db_values->pax_id = 1;
        $db_values->admin_id = 1;
        $db_values->modification_ts = 1;
        $db_values->id = 1;
        if (TestScoringInteractionTypes::tryFrom($type) !== null) {
            $this->assertInstanceOf(TestScoringInteraction::class, $this->testObj->buildScoringInteractionFromDBValues($db_values));
        } else {
            $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
            $this->testObj->buildScoringInteractionFromDBValues($db_values);
        }
    }

    public static function provideScoringInteractionType(): array
    {
        return [
            "dataset 1: valid type" => [
                "type" => "question_graded"
            ],
            "dataset 2: invalid type" => [
                "type" => "type"
            ]
        ];
    }

    public function test_buildError(): void
    {
        $this->assertInstanceOf(TestError::class, $this->testObj->buildError(1, 2, 3, 4, TestErrorTypes::ERROR_ON_PARTICIPANT_INTERACTION, "message"));
    }

    /**
     * @dataProvider provideErrorType
     */
    public function test_buildErrorFromDBValues($type): void
    {
        $db_values = $this->createMock(\stdClass::class);
        $db_values->interaction_type = $type;
        $db_values->qst_id = 1;
        $db_values->error_message = "test";
        $db_values->ref_id = 1;
        $db_values->pax_id = 1;
        $db_values->admin_id = 1;
        $db_values->modification_ts = 1;
        $db_values->id = 1;
        if (TestErrorTypes::tryFrom($type) !== null) {
            $this->assertInstanceOf(TestError::class, $this->testObj->buildErrorFromDBValues($db_values));
        } else {
            $this->expectExceptionMessage('Invalid Interaction Type in Database for id 1 with type ' . $type);
            $this->testObj->buildErrorFromDBValues($db_values);
        }
    }

    public static function provideErrorType(): array
    {
        return [
            "dataset 1: valid type" => [
                "type" => 'error_on_test_administration_interaction'
            ],
            "dataset 2: invalid type" => [
                "type" => "type"
            ]
        ];
    }


}
