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

class RequestProcessorListMetadataFormatsTest extends RequestProcessorTestCase
{
    public function testGetResponseToRequestListMetadataFormats(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListMetadataFormats:</response_info>
              <md_format>some metadata</md_format>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            []
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsWithIdentifier(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 0, 'id'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListMetadataFormats:identifier=prefix_id</response_info>
              <md_format>some metadata</md_format>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::IDENTIFIER->value => 'prefix_id']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsAdditionalArgumentError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListMetadataFormats:until=some date</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::UNTIL_DATE->value => 'some date'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsInvalidIdentifierError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 0, 'no prefix'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListMetadataFormats:identifier=no prefix</response_info>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::IDENTIFIER->value => 'no prefix']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsRecordNotFoundError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListMetadataFormats:identifier=prefix_id</response_info>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::IDENTIFIER->value => 'prefix_id']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsMultipleErrors(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListMetadataFormats:identifier=prefix_id,until=some date</response_info>
              <error>badArgument</error>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::IDENTIFIER->value => 'prefix_id', Argument::UNTIL_DATE->value => 'some date'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }
}
