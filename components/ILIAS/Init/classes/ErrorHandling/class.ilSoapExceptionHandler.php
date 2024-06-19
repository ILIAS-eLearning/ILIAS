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

class ilSoapExceptionHandler extends \Whoops\Handler\Handler
{
    private function buildFaultString(): string
    {
        if (!defined('DEVMODE') || DEVMODE !== 1) {
            return htmlspecialchars($this->getInspector()->getException()->getMessage());
        }

        $fault_string = \Whoops\Exception\Formatter::formatExceptionPlain($this->getInspector());
        $exception = $this->getInspector()->getException();
        $previous = $exception->getPrevious();
        while ($previous) {
            $fault_string .= "\n\nCaused by\n" . $this->getSimpleExceptionOutput($previous);
            $previous = $previous->getPrevious();
        }

        return htmlspecialchars($fault_string);
    }

    private function getSimpleExceptionOutput(Throwable $exception): string
    {
        return sprintf(
            '%s: %s in file %s on line %d',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }

    public function handle(): ?int
    {
        echo $this->toXml();

        return \Whoops\Handler\Handler::QUIT;
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
