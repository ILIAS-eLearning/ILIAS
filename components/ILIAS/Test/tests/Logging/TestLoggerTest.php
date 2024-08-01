<?php

namespace Logging;

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
use PHPUnit\Framework\MockObject\MockObject;

class TestLoggerTest extends ilTestBaseTestCase
{
    private TestLogger $testLogger;
    protected function setUp(): void
    {
        parent::setUp();

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingSettings
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $loggingSettings
            ->method('isIPLoggingEnabled')
            ->willReturn(true);

        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->method('testHasParticipantInteractions')
            ->willReturn(true);

        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);

        $this->testLogger = new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);
    }

    public function test_isLoggingEnabled(): void
    {
        $this->assertTrue($this->testLogger->isLoggingEnabled());
    }

    public function test_isIPLoggingEnabled(): void
    {
        $this->assertTrue($this->testLogger->isIPLoggingEnabled());
    }

    public function test_testHasParticipantInteractions(): void
    {
        $this->assertTrue($this->testLogger->testHasParticipantInteractions(777));
    }

    public function test_deleteParticipantInteractionsForTest(): void
    {
        $arg = 777;
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->once())
            ->method('deleteParticipantInteractionsForTest')
            ->with($arg);

        $testLogger = $this->createTestLoggerWithLoggingRepository($loggingRepository);
        $testLogger->deleteParticipantInteractionsForTest($arg);
    }

    public function test_logTestAdministrationInteraction(): void
    {
        $arg = $this->createMock(TestAdministrationInteraction::class);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->once())
            ->method('storeTestAdministrationInteraction')
            ->with($arg);

        $testLogger = $this->createTestLoggerWithLoggingRepository($loggingRepository);
        $testLogger->logTestAdministrationInteraction($arg);
    }

    public function test_logQuestionAdministrationInteraction(): void
    {
        $arg = $this->createMock(TestQuestionAdministrationInteraction::class);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->once())
            ->method('storeQuestionAdministrationInteraction')
            ->with($arg);

        $testLogger = $this->createTestLoggerWithLoggingRepository($loggingRepository);
        $testLogger->logQuestionAdministrationInteraction($arg);
    }

    public function test_logParticipantInteraction(): void
    {
        $arg = $this->createMock(TestParticipantInteraction::class);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->once())
            ->method('storeParticipantInteraction')
            ->with($arg);

        $testLogger = $this->createTestLoggerWithLoggingRepository($loggingRepository);
        $testLogger->logParticipantInteraction($arg);
    }

    public function test_logScoringInteraction(): void
    {
        $arg = $this->createMock(TestScoringInteraction::class);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->once())
            ->method('storeScoringInteraction')
            ->with($arg);

        $testLogger = $this->createTestLoggerWithLoggingRepository($loggingRepository);
        $testLogger->logScoringInteraction($arg);
    }

    /**
     * @dataProvider provideMethodNameEmergencyAlertCritical
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_emergency_alert_critical_withLogging(string $method): void
    {
        $message = "message";
        $context = ["ref_id" => 1];

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingSettings
            ->expects($this->once())
            ->method("isLoggingEnabled")
            ->willReturn(true);

        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);
        $componentLogger
            ->expects($this->once())
            ->method($method)
            ->with($message, $context);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->once())
            ->method("storeError");

        $testLogger = new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);

        $testLogger->$method($message, $context);

    }

    /**
     * @dataProvider provideMethodNameEmergencyAlertCritical
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_emergency_alert_critical_WithoutLogging(string $method): void
    {
        $message = "message";
        $context = ["ref_id" => 1];

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingSettings
            ->expects($this->once())
            ->method("isLoggingEnabled")
            ->willReturn(false);

        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);
        $componentLogger
            ->expects($this->once())
            ->method($method)
            ->with($message, $context);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->never())
            ->method("storeError");

        $testLogger = new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);
        $testLogger->$method($message, $context);

    }

    public static function provideMethodNameEmergencyAlertCritical(): array
    {
        return [
            "emergency" => ["method" => "emergency"],
            "alert" => ["method" => "alert"],
            "critical" => ["method" => "critical"]
        ];
    }

    public function test_log_withLogging(): void
    {
        $level = 400;
        $message = "message";
        $context = ["ref_id" => 1];

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingSettings
            ->expects($this->once())
            ->method("isLoggingEnabled")
            ->willReturn(true);

        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);
        $componentLogger
            ->expects($this->once())
            ->method("log")
            ->with($message, $level, $context);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->once())
            ->method("storeError");

        $testLogger = new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);

        $testLogger->log($level, $message, $context);

    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_log_WithoutLogging(): void
    {
        $level = 400;
        $message = "message";
        $context = ["ref_id" => 1];

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingSettings
            ->expects($this->once())
            ->method("isLoggingEnabled")
            ->willReturn(false);

        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);
        $componentLogger
            ->expects($this->once())
            ->method("log")
            ->with($message, $level, $context);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->never())
            ->method("storeError");

        $testLogger = new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);
        $testLogger->log($level, $message, $context);

    }

    public function test_error_withLogging(): void
    {
        $message = "message";
        $context = ["ref_id" => 1];

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingSettings
            ->expects($this->once())
            ->method("isLoggingEnabled")
            ->willReturn(true);

        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);
        $componentLogger
            ->expects($this->once())
            ->method("error")
            ->with($message, $context);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->once())
            ->method("storeError");

        $testLogger = new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);

        $testLogger->error($message, $context);

    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_error_WithoutLogging(): void
    {
        $message = "message";
        $context = ["ref_id" => 1];

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingSettings
            ->expects($this->once())
            ->method("isLoggingEnabled")
            ->willReturn(false);

        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);
        $componentLogger
            ->expects($this->never())
            ->method("error")
            ->with($message, $context);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $loggingRepository
            ->expects($this->never())
            ->method("storeError");

        $testLogger = new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);
        $testLogger->error($message, $context);

    }

    /**
     * @dataProvider provideMethodNameWarningNoticeInfoDebug
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_warning_notice_info_debug(string $method): void
    {
        $message = "message";
        $context = ["ref_id" => 1];

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);
        $componentLogger
            ->expects($this->once())
            ->method($method)
            ->with($message, $context);
        $loggingRepository = $this->createMock(TestLoggingRepository::class);

        $testLogger = new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);
        $testLogger->$method($message, $context);
    }

    public static function provideMethodNameWarningNoticeInfoDebug(): array
    {
        return [
            "warning" => ["method" => "warning"],
            "notice" => ["method" => "notice"],
            "info" => ["method" => "info"],
            "debug" => ["method" => "debug"]
        ];
    }

    public function test_getComponentLogger(): void
    {
        $logger = $this->testLogger->getComponentLogger();
        $this->assertInstanceOf(\ilComponentLogger::class, $logger);
        $this->assertInstanceOf(MockObject::class, $logger);
    }

    public function test_getInteractionFactory(): void
    {
        $factory = $this->testLogger->getInteractionFactory();
        $this->assertInstanceOf(Factory::class, $factory);
        $this->assertInstanceOf(MockObject::class, $factory);
    }

    public function test_getAdditionalInformationGenerator(): void
    {
        $generator = $this->testLogger->getAdditionalInformationGenerator();
        $this->assertInstanceOf(AdditionalInformationGenerator::class, $generator);
        $this->assertInstanceOf(MockObject::class, $generator);
    }

    public function test_getLogEntryTypes(): void
    {
        $result = [
            'tai',
            'qai',
            'pi',
            'si',
            'te'
        ];

        $this->assertSame($result, $this->testLogger->getLogEntryTypes());
    }

    public function test_getInteractionTypes(): void
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
                'question_modified_in_corrections',
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
        $this->assertSame($result, $this->testLogger->getInteractionTypes());
    }
    private function createTestLoggerWithLoggingRepository(TestLoggingRepository $loggingRepository): TestLogger
    {

        $loggingSettings = $this->createMock(TestLoggingSettings::class);
        $loggingFactory = $this->createMock(Factory::class);
        $infoGenerator = $this->createMock(AdditionalInformationGenerator::class);
        $componentLogger = $this->createMock(\ilComponentLogger::class);

        return new TestLogger($loggingSettings, $loggingRepository, $loggingFactory, $infoGenerator, $componentLogger);
    }
}
