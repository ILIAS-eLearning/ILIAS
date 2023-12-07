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

class TestLogger implements LoggerInterface
{
    public function __construct(
        private \ilComponentLogger $component_logger,
        private TestLoggingRepository $logging_repository
    ) {
    }

    public function logTestAdministrationInteraction(TestAdministrationInteraction $interaction): void
    {

    }

    public function logQuestionAdministrationInteraction(TestQuestionAdministrationInteraction $interaction): void
    {

    }

    public function logParticipantInteraction(TestParticipantInteraction $interaction): void
    {

    }

    public function logMarkingInteraction(TestMarkingInteraction $interaction): void
    {

    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {

    }
    public function alert(string|\Stringable $message, array $context = []): void
    {

    }

    public function critical(string|\Stringable $message, array $context = []): void
    {

    }
    public function error(string|\Stringable $message, array $context = []): void
    {

    }

    public function warning(string|\Stringable $message, array $context = []): void
    {

    }

    public function notice(string|\Stringable $message, array $context = []): void
    {

    }

    public function info(string|\Stringable $message, array $context = []): void
    {

    }

    public function debug(string|\Stringable $message, array $context = []): void
    {

    }

    public function log($level, string|\Stringable $message, mixed $context = []): void
    {

    }
}
