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

namespace ILIAS\MetaData\OERExposer\OAIPMH\Responses;

use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;

class RequestProcessorBadVerbTest extends RequestProcessorTestCase
{
    public function testGetResponseToRequestBadVerbError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:NoVerb:</response_info>
              <error>badVerb</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest('base url', Verb::NULL, []));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }
}
