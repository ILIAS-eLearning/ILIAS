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

namespace ILIAS\Init\ErrorHandling\Application;

use ILIAS\Init\ErrorHandling\Incident\ErrorIncident;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentFactory;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentRegistry;
use Whoops\Exception\Inspector;

/**
 * Reports an exception as a logged error incident and registers it for other handlers.
 */
final class ReportErrorIncident implements ErrorIncidentReporting
{
    public function __construct(
        private readonly ErrorLogDirectory $log_directory,
        private readonly ErrorLogFileStorage $log_file_storage,
        private readonly ErrorIncidentFactory $incident_factory,
        private readonly ErrorIncidentRegistry $incident_registry,
        /** @var list<string> */
        private readonly array $sensitive_parameter_names
    ) {
    }

    public function report(Inspector $inspector): ?ErrorIncident
    {
        $directory = $this->log_directory->path();
        if ($directory === '') {
            return null;
        }

        $incident = $this->incident_factory->create(session_id());
        $this->log_file_storage->write(
            $inspector,
            $directory,
            $incident->identifier()->value(),
            $this->sensitive_parameter_names
        );
        $this->incident_registry->record($incident);

        return $incident;
    }
}
