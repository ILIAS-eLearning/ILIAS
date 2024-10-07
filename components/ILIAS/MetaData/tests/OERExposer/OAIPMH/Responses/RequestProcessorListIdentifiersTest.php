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

class RequestProcessorListIdentifiersTest extends RequestProcessorTestCase
{
    public function testGetResponseToRequestListIdentifiers(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc</response_info>
              <header>prefix_id1+2022-11-27:2022-11-27</header>
              <header>prefix_id2+2022-11-27:2022-11-27</header>
              <header>prefix_id3+2021-11-13:2021-11-13</header>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc'],
        ));

        $this->assertEquals(
            [['from' => null, 'until' => null, 'limit' => 100, 'offset' => 0]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersWithFromDate(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc,from=2019-01-02</response_info>
              <header>prefix_id1+2022-11-27:2022-11-27</header>
              <header>prefix_id2+2022-11-27:2022-11-27</header>
              <header>prefix_id3+2021-11-13:2021-11-13</header>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc', Argument::FROM_DATE->value => '2019-01-02'],
        ));

        $this->assertEquals(
            [['from' => '2019-01-02', 'until' => null, 'limit' => 100, 'offset' => 0]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersWithUntilDate(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc,until=2033-11-02</response_info>
              <header>prefix_id1+2022-11-27:2022-11-27</header>
              <header>prefix_id2+2022-11-27:2022-11-27</header>
              <header>prefix_id3+2021-11-13:2021-11-13</header>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc', Argument::UNTIL_DATE->value => '2033-11-02'],
        ));

        $this->assertSame(
            [['from' => null, 'until' => '2033-11-02', 'limit' => 100, 'offset' => 0]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersWithBothDates(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc,until=2033-11-02,from=2019-01-02</response_info>
              <header>prefix_id1+2022-11-27:2022-11-27</header>
              <header>prefix_id2+2022-11-27:2022-11-27</header>
              <header>prefix_id3+2021-11-13:2021-11-13</header>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [
                Argument::MD_PREFIX->value => 'oai_dc',
                Argument::UNTIL_DATE->value => '2033-11-02',
                Argument::FROM_DATE->value => '2019-01-02'
            ],
        ));

        $this->assertEquals(
            [['from' => '2019-01-02', 'until' => '2033-11-02', 'limit' => 100, 'offset' => 0]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersIncompleteList(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                203,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc</response_info>
              <header>prefix_id1+2022-11-27:2022-11-27</header>
              <header>prefix_id2+2022-11-27:2022-11-27</header>
              <header>prefix_id3+2021-11-13:2021-11-13</header>
              <token>next_offset=100:from=:until=,fullsize=203,cursor=0</token>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc'],
        ));

        $this->assertEquals(
            [['from' => null, 'until' => null, 'limit' => 100, 'offset' => 0]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersWithResumptionToken(): void
    {
        $original_request = $this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::RESUMPTION_TOKEN->value => 'next_offset=100:from=:until=2024-07-28'],
        );

        $appended_request = $this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [
                Argument::RESUMPTION_TOKEN->value => 'next_offset=100:from=:until=2024-07-28',
                Argument::UNTIL_DATE->value => '2024-07-28'
            ],
        );

        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                203,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler(true, $appended_request)
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListIdentifiers:resumptionToken=next_offset=100:from=:until=2024-07-28</response_info>
              <header>prefix_id1+2022-11-27:2022-11-27</header>
              <header>prefix_id2+2022-11-27:2022-11-27</header>
              <header>prefix_id3+2021-11-13:2021-11-13</header>
              <token>next_offset=200:from=:until=2024-07-28,fullsize=203,cursor=100</token>
            </response>
            XML;

        $response = $processor->getResponseToRequest($original_request);

        $this->assertEquals(
            [['from' => null, 'until' => '2024-07-28', 'limit' => 100, 'offset' => 100]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersWithResumptionTokenContainingFromDate(): void
    {
        $original_request = $this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::RESUMPTION_TOKEN->value => 'next_offset=100:from=1999-01-12:until=2024-07-28'],
        );

        $appended_request = $this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [
                Argument::RESUMPTION_TOKEN->value => 'next_offset=100:from=1999-01-12:until=2024-07-28',
                Argument::UNTIL_DATE->value => '2024-07-28',
                Argument::FROM_DATE->value => '1999-01-12'
            ],
        );

        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                203,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler(true, $appended_request)
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListIdentifiers:resumptionToken=next_offset=100:from=1999-01-12:until=2024-07-28</response_info>
              <header>prefix_id1+2022-11-27:2022-11-27</header>
              <header>prefix_id2+2022-11-27:2022-11-27</header>
              <header>prefix_id3+2021-11-13:2021-11-13</header>
              <token>next_offset=200:from=1999-01-12:until=2024-07-28,fullsize=203,cursor=100</token>
            </response>
            XML;

        $response = $processor->getResponseToRequest($original_request);

        $this->assertEquals(
            [['from' => '1999-01-12', 'until' => '2024-07-28', 'limit' => 100, 'offset' => 100]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersWithResumptionTokenLastIncompleteList(): void
    {
        $original_request = $this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::RESUMPTION_TOKEN->value => 'next_offset=200:from=:until=2024-07-28'],
        );

        $appended_request = $this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [
                Argument::RESUMPTION_TOKEN->value => 'next_offset=200:from=:until=2024-07-28',
                Argument::UNTIL_DATE->value => '2024-07-28'
            ],
        );

        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                203,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler(true, $appended_request)
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListIdentifiers:resumptionToken=next_offset=200:from=:until=2024-07-28</response_info>
              <header>prefix_id1+2022-11-27:2022-11-27</header>
              <header>prefix_id2+2022-11-27:2022-11-27</header>
              <header>prefix_id3+2021-11-13:2021-11-13</header>
              <token>,fullsize=203,cursor=200</token>
            </response>
            XML;

        $response = $processor->getResponseToRequest($original_request);

        $this->assertEquals(
            [['from' => null, 'until' => '2024-07-28', 'limit' => 100, 'offset' => 200]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersNoMDFormatError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersAdditionalArgumentError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc,identifier=id</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc', Argument::IDENTIFIER->value => 'id'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersBadResumptionTokenError(): void
    {
        $original_request = $this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::RESUMPTION_TOKEN->value => 'next_offset=100:from=:until=2024-07-28'],
        );

        $appended_request = $this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [
                Argument::RESUMPTION_TOKEN->value => 'next_offset=100:from=:until=2024-07-28',
                Argument::UNTIL_DATE->value => '2024-07-28'
            ],
        );

        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                203,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler(false, $appended_request)
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:resumptionToken=next_offset=100:from=:until=2024-07-28</response_info>
              <error>badResumptionToken</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($original_request);

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersInvalidFromDateError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc,from=invalid</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc', Argument::FROM_DATE->value => 'invalid'],
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersInvalidUntilDateError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc,until=invalid</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc', Argument::UNTIL_DATE->value => 'invalid'],
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersWrongMDFormatError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:metadataPrefix=invalid</response_info>
              <error>cannotDisseminateFormat</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'invalid'],
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersNoRecordsFoundError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc</response_info>
              <error>noRecordsMatch</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc'],
        ));

        $this->assertEquals(
            [['from' => null, 'until' => null, 'limit' => 100, 'offset' => 0]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersNoSetsError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(
                null,
                3,
                'id1+2022-11-27',
                'id2+2022-11-27',
                'id3+2021-11-13'
            ),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:metadataPrefix=oai_dc,set=set</response_info>
              <error>noSetHierarchy</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [Argument::MD_PREFIX->value => 'oai_dc', Argument::SET->value => 'set'],
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListIdentifiersMultipleErrors(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $repo = $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListIdentifiers:metadataPrefix=invalid,until=also invalid,from=more invalid,set=set,identifier=id</response_info>
              <error>badArgument</error>
              <error>noSetHierarchy</error>
              <error>cannotDisseminateFormat</error>
              <error>badArgument</error>
              <error>badArgument</error>
              <error>noRecordsMatch</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_IDENTIFIERS,
            [
                Argument::MD_PREFIX->value => 'invalid',
                Argument::UNTIL_DATE->value => 'also invalid',
                Argument::FROM_DATE->value => 'more invalid',
                Argument::SET->value => 'set',
                Argument::IDENTIFIER->value => 'id'
            ],
            false
        ));

        $this->assertEquals(
            [['from' => null, 'until' => null, 'limit' => 100, 'offset' => 0]],
            $repo->exposed_parameters
        );
        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }
}
