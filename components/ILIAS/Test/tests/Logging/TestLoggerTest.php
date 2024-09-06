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

use ilComponentLogger;
use ILIAS\Test\Administration\TestLoggingSettings;
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\Logging\Factory;
use ILIAS\Test\Logging\TestAdministrationInteraction;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestLoggingRepository;
use ILIAS\Test\Logging\TestParticipantInteraction;
use ILIAS\Test\Logging\TestQuestionAdministrationInteraction;
use ILIAS\Test\Logging\TestScoringInteraction;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class TestLoggerTest extends ilTestBaseTestCase
{
    private TestLogger $test_logger;

    /**
     * @throws Exception|ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $logging_settings = $this->createMock(TestLoggingSettings::class);
        $logging_settings
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $logging_settings
            ->method('isIPLoggingEnabled')
            ->willReturn(true);

        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->method('testHasParticipantInteractions')
            ->willReturn(true);

        $this->test_logger = $this->createInstanceOf(
            TestLogger::class,
            [
                'logging_settings' => $logging_settings,
                'logging_repository' => $logging_repository
            ]
        );
    }

    public function testIsLoggingEnabled(): void
    {
        $this->assertTrue($this->test_logger->isLoggingEnabled());
    }

    public function testIsIPLoggingEnabled(): void
    {
        $this->assertTrue($this->test_logger->isIPLoggingEnabled());
    }

    public function testTestHasParticipantInteractions(): void
    {
        $this->assertTrue($this->test_logger->testHasParticipantInteractions(777));
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testDeleteParticipantInteractionsForTest(): void
    {
        $arg = 777;
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->once())
            ->method('deleteParticipantInteractionsForTest')
            ->with($arg);

        $test_logger = $this->createInstanceOf(TestLogger::class, ['logging_repository' => $logging_repository]);
        $test_logger->deleteParticipantInteractionsForTest($arg);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testLogTestAdministrationInteraction(): void
    {
        $arg = $this->createMock(TestAdministrationInteraction::class);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->once())
            ->method('storeTestAdministrationInteraction')
            ->with($arg);

        $test_logger = $this->createInstanceOf(TestLogger::class, ['logging_repository' => $logging_repository]);
        $test_logger->logTestAdministrationInteraction($arg);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testLogQuestionAdministrationInteraction(): void
    {
        $arg = $this->createMock(TestQuestionAdministrationInteraction::class);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->once())
            ->method('storeQuestionAdministrationInteraction')
            ->with($arg);

        $test_logger = $this->createInstanceOf(TestLogger::class, ['logging_repository' => $logging_repository]);
        $test_logger->logQuestionAdministrationInteraction($arg);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testLogParticipantInteraction(): void
    {
        $arg = $this->createMock(TestParticipantInteraction::class);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->once())
            ->method('storeParticipantInteraction')
            ->with($arg);

        $test_logger = $this->createInstanceOf(TestLogger::class, ['logging_repository' => $logging_repository]);
        $test_logger->logParticipantInteraction($arg);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testLogScoringInteraction(): void
    {
        $arg = $this->createMock(TestScoringInteraction::class);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->once())
            ->method('storeScoringInteraction')
            ->with($arg);

        $test_logger = $this->createInstanceOf(TestLogger::class, ['logging_repository' => $logging_repository]);
        $test_logger->logScoringInteraction($arg);
    }

    /**
     * @dataProvider emergencyAlertCriticalWithLoggingDataProvider
     * @throws Exception|ReflectionException
     */
    public function testEmergencyAlertCriticalWithLogging(string $input): void
    {
        $message = 'message';
        $context = ['ref_id' => 1];

        $logging_settings = $this->createMock(TestLoggingSettings::class);
        $logging_settings
            ->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn(true);

        $component_logger = $this->createMock(ilComponentLogger::class);
        $component_logger
            ->expects($this->once())
            ->method($input)
            ->with($message, $context);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->once())
            ->method('storeError');

        $test_logger = $this->createInstanceOf(
            TestLogger::class, [
                'logging_settings' => $logging_settings,
                'logging_repository' => $logging_repository,
                'component_logger' => $component_logger
            ]
        );
        $test_logger->$input($message, $context);
    }

    /**
     * @dataProvider emergencyAlertCriticalWithLoggingDataProvider
     * @throws Exception|ReflectionException
     */
    public function testEmergencyAlertCriticalWithoutLogging(string $input): void
    {
        $message = 'message';
        $context = ['ref_id' => 1];

        $logging_settings = $this->createMock(TestLoggingSettings::class);
        $logging_settings
            ->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn(false);

        $component_logger = $this->createMock(ilComponentLogger::class);
        $component_logger
            ->expects($this->once())
            ->method($input)
            ->with($message, $context);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->never())
            ->method('storeError');

        $test_logger = $this->createInstanceOf(
            TestLogger::class, [
                'logging_settings' => $logging_settings,
                'logging_repository' => $logging_repository,
                'component_logger' => $component_logger
            ]
        );
        $test_logger->$input($message, $context);
    }

    public static function emergencyAlertCriticalWithLoggingDataProvider(): array
    {
        return [
            'emergency' => ['emergency'],
            'alert' => ['alert'],
            'critical' => ['critical']
        ];
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testLogWithLogging(): void
    {
        $level = 400;
        $message = 'message';
        $context = ['ref_id' => 1];

        $logging_settings = $this->createMock(TestLoggingSettings::class);
        $logging_settings
            ->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn(true);

        $component_logger = $this->createMock(ilComponentLogger::class);
        $component_logger
            ->expects($this->once())
            ->method('log')
            ->with($message, $level, $context);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->once())
            ->method('storeError');

        $test_logger = $this->createInstanceOf(
            TestLogger::class, [
                'logging_settings' => $logging_settings,
                'logging_repository' => $logging_repository,
                'component_logger' => $component_logger
            ]
        );
        $test_logger->log($level, $message, $context);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testLogWithoutLogging(): void
    {
        $level = 400;
        $message = 'message';
        $context = ['ref_id' => 1];

        $logging_settings = $this->createMock(TestLoggingSettings::class);
        $logging_settings
            ->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn(false);

        $component_logger = $this->createMock(ilComponentLogger::class);
        $component_logger
            ->expects($this->once())
            ->method('log')
            ->with($message, $level, $context);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->never())
            ->method('storeError');

        $test_logger = $this->createInstanceOf(
            TestLogger::class, [
                'logging_settings' => $logging_settings,
                'logging_repository' => $logging_repository,
                'component_logger' => $component_logger
            ]
        );
        $test_logger->log($level, $message, $context);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testErrorWithLogging(): void
    {
        $message = 'message';
        $context = ['ref_id' => 1];

        $logging_settings = $this->createMock(TestLoggingSettings::class);
        $logging_settings
            ->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn(true);

        $component_logger = $this->createMock(ilComponentLogger::class);
        $component_logger
            ->expects($this->once())
            ->method('error')
            ->with($message, $context);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->once())
            ->method('storeError');

        $test_logger = $this->createInstanceOf(
            TestLogger::class, [
                'logging_settings' => $logging_settings,
                'logging_repository' => $logging_repository,
                'component_logger' => $component_logger
            ]
        );
        $test_logger->error($message, $context);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testErrorWithoutLogging(): void
    {
        $message = 'message';
        $context = ['ref_id' => 1];

        $logging_settings = $this->createMock(TestLoggingSettings::class);
        $logging_settings
            ->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn(false);

        $component_logger = $this->createMock(ilComponentLogger::class);
        $component_logger
            ->expects($this->never())
            ->method('error')
            ->with($message, $context);
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $logging_repository
            ->expects($this->never())
            ->method('storeError');

        $test_logger = $this->createInstanceOf(
            TestLogger::class, [
                'logging_settings' => $logging_settings,
                'logging_repository' => $logging_repository,
                'component_logger' => $component_logger
            ]
        );
        $test_logger->error($message, $context);
    }

    /**
     * @dataProvider warningNoticeInfoDebugDataProvider
     * @throws Exception|ReflectionException
     */
    public function testWarningNoticeInfoDebug(string $method): void
    {
        $message = 'message';
        $context = ['ref_id' => 1];

        $component_logger = $this->createMock(ilComponentLogger::class);
        $component_logger
            ->expects($this->once())
            ->method($method)
            ->with($message, $context);

        $test_logger = $this->createInstanceOf(TestLogger::class, ['component_logger' => $component_logger]);
        $test_logger->$method($message, $context);
    }

    public static function warningNoticeInfoDebugDataProvider(): array
    {
        return [
            'warning' => ['warning'],
            'notice' => ['notice'],
            'info' => ['info'],
            'debug' => ['debug']
        ];
    }

    public function testGetComponentLogger(): void
    {
        $logger = $this->test_logger->getComponentLogger();
        $this->assertInstanceOf(ilComponentLogger::class, $logger);
        $this->assertInstanceOf(MockObject::class, $logger);
    }

    public function testGetInteractionFactory(): void
    {
        $factory = $this->test_logger->getInteractionFactory();
        $this->assertInstanceOf(Factory::class, $factory);
        $this->assertInstanceOf(MockObject::class, $factory);
    }

    public function testGetAdditionalInformationGenerator(): void
    {
        $generator = $this->test_logger->getAdditionalInformationGenerator();
        $this->assertInstanceOf(AdditionalInformationGenerator::class, $generator);
        $this->assertInstanceOf(MockObject::class, $generator);
    }

    public function testGetLogEntryTypes(): void
    {
        $result = [
            'tai',
            'qai',
            'pi',
            'si',
            'te'
        ];

        $this->assertSame($result, $this->test_logger->getLogEntryTypes());
    }

    public function testGetInteractionTypes(): void
    {
        $result = [
            'tai' => [
                'new_test_created',
                'main_settings_modified',
                'scoring_settings_modified',
                'mark_schema_modified',
                'mark_schema_reset',
                'question_selection_criteria_modified',
                'question_added',
                'question_moved',
                'question_removed',
                'question_removed_in_corrections',
                'question_synchronisation_reset',
                'questions_synchronised',
                'extra_time_added',
                'test_run_of_participant_closed',
                'participant_data_removed',
                'test_deleted'
            ],
            'qai' => [
                'question_modified',
                'question_modified_in_corrections'
            ],
            'pi' => [
                'wrong_test_password_provided',
                'test_run_started',
                'question_shown',
                'question_skipped',
                'answer_submitted',
                'answer_deleted',
                'test_run_finished'
            ],
            'si' => [
                'question_graded',
                'question_grading_reset'
            ],
            'te' => [
                'error_on_test_administration_interaction',
                'error_on_question_administration_interaction',
                'error_on_participant_interaction',
                'error_on_scoring_interaction',
                'error_on_undefined_interaction'
            ]
        ];

        $this->assertSame($result, $this->test_logger->getInteractionTypes());
    }
}
