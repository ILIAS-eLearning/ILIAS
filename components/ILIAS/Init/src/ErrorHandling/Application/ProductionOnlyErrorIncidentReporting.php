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
use Whoops\Exception\Inspector;

/**
 * Suppresses incident reporting (and thus log file writing) while devmode is active,
 * so that error log files are only created in production.
 */
final readonly class ProductionOnlyErrorIncidentReporting implements ErrorIncidentReporting
{
    public function __construct(
        private ErrorIncidentReporting $reporting,
        private DevmodeState $devmode_state
    ) {
    }

    public function report(Inspector $inspector): ?ErrorIncident
    {
        if ($this->devmode_state->isActive()) {
            return null;
        }

        return $this->reporting->report($inspector);
    }
}
