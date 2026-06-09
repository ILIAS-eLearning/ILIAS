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

namespace ILIAS\Init\ErrorHandling\Infrastructure\Whoops;

use ILIAS\Init\ErrorHandling\Application\ErrorIncidentReporting;
use Whoops\Handler\Handler;

/**
 * Whoops handler that reports the exception (without quitting) as a logged error incident.
 */
final class RecordErrorIncidentHandler extends Handler
{
    public function __construct(
        private readonly ErrorIncidentReporting $error_incident_reporting
    ) {
    }

    public function handle(): ?int
    {
        $this->error_incident_reporting->report($this->getInspector());

        return null;
    }
}
