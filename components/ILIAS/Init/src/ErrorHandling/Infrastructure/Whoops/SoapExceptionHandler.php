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

use ILIAS\Init\ErrorHandling\Application\DevmodeState;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentRegistry;
use Throwable;
use Whoops\Exception\Formatter;
use Whoops\Handler\Handler;

/**
 * Whoops handler that renders SOAP fault responses for SOAP POST requests.
 */
final class SoapExceptionHandler extends Handler
{
    public function __construct(
        private readonly ErrorIncidentRegistry $incident_registry,
        private readonly DevmodeState $devmode_state
    ) {
    }

    private function buildFaultString(): string
    {
        $incident = $this->incident_registry->current();

        if ($this->devmode_state->isActive()) {
            $fault_string = Formatter::formatExceptionPlain($this->getInspector());
            $exception = $this->getInspector()->getException();
            $previous = $exception->getPrevious();
            while ($previous) {
                $fault_string .= "\n\nCaused by\n" . $this->getSimpleExceptionOutput($previous);
                $previous = $previous->getPrevious();
            }
        } else {
            $fault_string = $this->getInspector()->getException()->getMessage();
        }

        if ($incident !== null) {
            $fault_string .= "\n\n (incident code: " . $incident->identifier()->value() . ')';
        }

        return htmlspecialchars($fault_string);
    }

    private function getSimpleExceptionOutput(Throwable $exception): string
    {
        return \sprintf(
            '%s: %s in file %s on line %d',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }

    public function handle(): ?int
    {
        echo $this->toXml();

        return Handler::QUIT;
    }

    private function toXml(): string
    {
        $fault_code = htmlspecialchars((string) $this->getInspector()->getException()->getCode());
        $fault_string = $this->buildFaultString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
        $xml .= '  <SOAP-ENV:Body>';
        $xml .= '    <SOAP-ENV:Fault>';
        $xml .= '      <faultcode>' . $fault_code . '</faultcode>';
        $xml .= '      <faultstring>' . $fault_string . '</faultstring>';
        $xml .= '    </SOAP-ENV:Fault>';
        $xml .= '  </SOAP-ENV:Body>';
        $xml .= '</SOAP-ENV:Envelope>';

        return $xml;
    }
}
