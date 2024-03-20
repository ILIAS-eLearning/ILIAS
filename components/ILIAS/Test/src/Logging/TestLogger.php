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

namespace ILIAS\Test\Logging;

use Psr\Log\LoggerInterface;

use ILIAS\Test\Administration\TestLoggingSettings;

class TestLogger implements LoggerInterface
{
    private const LOG_ENTRY_TYPES = [
        'test_administration_interaction' => TestAdministrationInteractionTypes::class,
        'question_administration_interaction' => TestQuestionAdministrationInteractionTypes::class,
        'participant_interaction' => TestParticipantInteractionTypes::class,
        'scoring_interaction' => TestScoringInteractionTypes::class,
        'test_error' => TestErrorTypes::class
    ];
    public function __construct(
        private readonly TestLoggingSettings $logging_settings,
        private readonly TestLoggingRepository $logging_repository,
        private readonly Factory $interaction_factory,
        private readonly \ilComponentLogger $component_logger,
        private readonly \ilLanguage $lng
    ) {
    }

    public function isLoggingEnabled(): bool
    {
        return $this->logging_settings->isLoggingEnabled();
    }

    public function logTestAdministrationInteraction(TestAdministrationInteraction $interaction): void
    {
        $this->logging_repository->storeTestAdministrationInteraction($interaction);
    }

    public function logQuestionAdministrationInteraction(TestQuestionAdministrationInteraction $interaction): void
    {
        $this->logging_repository->storeQuestionAdministrationInteraction($interaction);
    }

    public function logParticipantInteraction(TestParticipantInteraction $interaction): void
    {
        $this->logging_repository->storeParticipantInteraction($interaction);
    }

    public function logScoringInteraction(TestScoringInteraction $interaction): void
    {
        $this->logging_repository->storeScoringInteraction($interaction);
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->component_logger->emergency($message, $context);

        if (!$this->logging_settings->isLoggingEnabled()
            || !isset($context['ref_id'])) {
            return;
        }

        $this->logging_repository->storeError(
            $this->createTestErrorFromContext($context, $message)
        );
    }
    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->component_logger->alert($message, $context);

        if (!$this->logging_settings->isLoggingEnabled()
            || !isset($context['ref_id'])) {
            return;
        }

        $this->logging_repository->storeError(
            $this->createTestErrorFromContext($context, $message)
        );
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->component_logger->critical($message, $context);

        if (!$this->logging_settings->isLoggingEnabled()
            || !isset($context['ref_id'])) {
            return;
        }

        $this->logging_repository->storeError(
            $this->createTestErrorFromContext($context, $message)
        );
    }
    public function error(string|\Stringable $message, array $context = []): void
    {

        if (!$this->logging_settings->isLoggingEnabled()
            || !isset($context['ref_id'])) {
            return;
        }

        $this->logging_repository->storeError(
            $this->createTestErrorFromContext($context, $message)
        );
        $this->component_logger->error($message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->component_logger->warning($message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->component_logger->notice($message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->component_logger->info($message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->component_logger->debug($message, $context);
    }

    public function log($level, string|\Stringable $message, mixed $context = []): void
    {
        $this->component_logger->log($message, $level, $context);

        if (!$this->logging_settings->isLoggingEnabled()
            || intval($level) < \ilLogLevel::ERROR
            || !isset($context['ref_id'])) {
            return;
        }

        $this->logging_repository->storeError(
            $this->createTestErrorFromContext($context, $message)
        );
    }

    public function getComponentLogger(): \ilComponentLogger
    {
        $this->component_logger;
    }

    public function getInteractionFactory(): Factory
    {
        return $this->interaction_factory;
    }

    /**
     * @return array<string>
     */
    public function getLogEntryTypes(): array
    {
        return array_keys(self::LOG_ENTRY_TYPES);
    }

    public function getInteractionTypes(): array
    {
        $interaction_types = [];
        foreach (self::LOG_ENTRY_TYPES as $type => $enum_class) {
            $interaction_types[$type] = array_column($enum_class::cases(), 'value');
        }
        return $interaction_types;
    }

    private function createTestErrorFromContext(array $context, string $message): TestError
    {
        return new TestError(
            $this->lng,
            $context['ref_id'],
            $context ['question_id'] ?? null,
            $context['administrator'] ?? null,
            $context['participant'] ?? null,
            $context['error_type'] ?? TestErrorTypes::ERROR_ON_UNDEFINED_INTERACTION,
            $context['timestamp'] ?? time(),
            $message
        );
    }
}
