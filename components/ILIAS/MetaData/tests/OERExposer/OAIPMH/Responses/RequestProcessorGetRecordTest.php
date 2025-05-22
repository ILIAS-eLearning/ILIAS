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

class RequestProcessorGetRecordTest extends RequestProcessorTestCase
{
    public function testGetResponseToRequestGetRecord(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 0, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=oai_dc</response_info>
              <record>
                <record_info>prefix_id+2022-11-27:2022-11-27</record_info>
                <md>md for id+2022-11-27</md>
              </record>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'oai_dc']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordNoMDFormatError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 0, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordNoIdentifierError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 0, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:metadataPrefix=oai_dc</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::MD_PREFIX->value => 'oai_dc'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordAdditionalArgumentError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 0, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=oai_dc,from=date</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'oai_dc', Argument::FROM_DATE->value => 'date'],
            false
        ));


        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordWrongMDFormatError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 0, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=invalid</response_info>
              <error>cannotDisseminateFormat</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'invalid']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordInvalidIdentifierError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 0, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=invalid_id+2022-11-27,metadataPrefix=oai_dc</response_info>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'invalid_id+2022-11-27', Argument::MD_PREFIX->value => 'oai_dc']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordNotFoundError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=oai_dc</response_info>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'oai_dc']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordMultipleErrors(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=invalid,from=date</response_info>
              <error>badArgument</error>
              <error>cannotDisseminateFormat</error>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'invalid', Argument::FROM_DATE->value => 'date'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }
}
