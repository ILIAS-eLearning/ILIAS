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

namespace ILIAS\AuthSOAP;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

readonly class ConnectionTester
{
    public function __construct(
        private \ilSetting $settings,
        private Factory $ui_factory,
    ) {
    }

    /**
     * @return list<Component>
     */
    public function testConnection(string $ext_uid, string $soap_pw, bool $new_user): array
    {
        $client = (new SoapAuthEndpoint($this->settings))->createValidationClient();

        try {
            $result = $client->validateSession($ext_uid, $soap_pw, $new_user);
        } catch (\SoapFault $e) {
            return [
                $this->ui_factory->messageBox()->failure(
                    'SOAP Authentication Call Error: ' . $e->getMessage()
                )
            ];
        }

        $validation = $result->validation;
        $is_valid = (bool) ($validation['valid'] ?? false);
        $status = $is_valid
            ? $this->ui_factory->messageBox()->success('SOAP authentication succeeded.')
            : $this->ui_factory->messageBox()->info('SOAP authentication returned invalid credentials.');

        return [
            $status,
            $this->ui_factory->listing()->descriptive([
                'Valid' => $is_valid ? 'true' : 'false',
                'First Name' => (string) ($validation['firstname'] ?? ''),
                'Last Name' => (string) ($validation['lastname'] ?? ''),
                'Email' => (string) ($validation['email'] ?? ''),
                'Request XML' => $this->renderXmlBlock($result->request),
                'Response XML' => $this->renderXmlBlock($result->response),
            ]),
        ];
    }

    private function renderXmlBlock(string $xml): Component
    {
        return $this->ui_factory->legacy(
            $this->formatXmlForDisplay($xml)
        );
    }

    private function formatXmlForDisplay(string $xml): string
    {
        $formatted = $this->prettyPrintXml($xml);
        $escaped = htmlspecialchars($formatted, ENT_QUOTES);
        $escaped = str_replace(' ', '&nbsp;', $escaped);

        return nl2br($escaped, false);
    }

    private function prettyPrintXml(string $xml): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        if (@$dom->loadXML($xml) === false) {
            return str_replace(['>', '" '], [">\n", "\"\n "], $xml);
        }

        return (string) $dom->saveXML();
    }
}
