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

class RequestProcessorListSetsTest extends RequestProcessorTestCase
{
    public function testGetResponseToRequestListSetsNoSetsError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListSets:</response_info>
              <error>noSetHierarchy</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest('base url', Verb::LIST_SETS, []));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListSetsNoSetsAndAdditionalArgumentError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListSets:identifier=some id</response_info>
              <error>badArgument</error>
              <error>noSetHierarchy</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_SETS,
            [Argument::IDENTIFIER->value => 'some id'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListSetsWithTokenNoSetsError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListSets:resumptionToken=token</response_info>
              <error>noSetHierarchy</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_SETS,
            [Argument::RESUMPTION_TOKEN->value => 'token']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }
}
