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

use PHPUnit\Framework\TestCase;
use ILIAS\Data\URI;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\Error;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\NullRequest;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;

class WriterTest extends TestCase
{
    protected function getURI(string $string): URI
    {
        $url = $this->createMock(URI::class);
        $url->method('__toString')->willReturn($string);
        return $url;
    }

    /**
     * argument values are just the names of the values prepended with '_val'
     */
    protected function getRequest(
        string $base_url,
        Verb $verb,
        Argument ...$arguments,
    ): RequestInterface {
        $base_url = $this->getURI($base_url);

        return new class ($base_url, $verb, $arguments) extends NullRequest {
            public function __construct(
                protected URI $base_url,
                protected Verb $verb,
                protected array $arguments
            ) {
            }

            public function baseURL(): URI
            {
                return $this->base_url;
            }

            public function verb(): Verb
            {
                return $this->verb;
            }

            public function argumentKeys(): \Generator
            {
                yield from $this->arguments;
            }

            public function argumentValue(Argument $argument): string
            {
                if (in_array($argument, $this->arguments)) {
                    return $argument->value . '_val';
                }
                return '';
            }
        };
    }

    protected function getWriter(string $current_time = '@0'): Writer
    {
        return new class ($current_time) extends Writer {
            public function __construct(protected string $current_time)
            {
            }

            protected function getCurrentDateTime(): \DateTimeImmutable
            {
                return new \DateTimeImmutable($this->current_time, new \DateTimeZone('UTC'));
            }
        };
    }

    public function testWriteError(): void
    {
        $expected_xml = <<<XML
            <error code="badVerb">some message</error>
            XML;

        $writer = $this->getWriter();
        $xml = $writer->writeError(Error::BAD_VERB, 'some message');

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->saveXML());
    }

    public function testWriteIdentifyElementsOneAdmin(): void
    {
        $expected_xmls = [
            <<<XML
            <repositoryName>my repository</repositoryName>
            XML,
            <<<XML
            <baseURL>http://www.my.org/test/repository</baseURL>
            XML,
            <<<XML
            <protocolVersion>2.0</protocolVersion>
            XML,
            <<<XML
            <earliestDatestamp>2017-11-04</earliestDatestamp>
            XML,
            <<<XML
            <deletedRecord>no</deletedRecord>
            XML,
            <<<XML
            <granularity>YYYY-MM-DD</granularity>
            XML,
            <<<XML
            <adminEmail>somemail@my.org</adminEmail>
            XML
        ];

        $writer = $this->getWriter();
        $xmls = $writer->writeIdentifyElements(
            'my repository',
            $this->getURI('http://www.my.org/test/repository'),
            new \DateTimeImmutable('2017-11-04', new \DateTimeZone('UTC')),
            'somemail@my.org'
        );

        $index = 0;
        foreach ($xmls as $xml) {
            if ($index < count($expected_xmls)) {
                $this->assertXmlStringEqualsXmlString($expected_xmls[$index], $xml->saveXML());
            }
            $index++;
        }
        $this->assertSame(count($expected_xmls), $index);
    }
    public function testWriteIdentifyElementsMultipleAdmins(): void
    {
        $expected_xmls = [
            <<<XML
            <repositoryName>my repository</repositoryName>
            XML,
            <<<XML
            <baseURL>http://www.my.org/test/repository</baseURL>
            XML,
            <<<XML
            <protocolVersion>2.0</protocolVersion>
            XML,
            <<<XML
            <earliestDatestamp>2017-11-04</earliestDatestamp>
            XML,
            <<<XML
            <deletedRecord>no</deletedRecord>
            XML,
            <<<XML
            <granularity>YYYY-MM-DD</granularity>
            XML,
            <<<XML
            <adminEmail>somemail@my.org</adminEmail>
            XML,
            <<<XML
            <adminEmail>othermail@my.org</adminEmail>
            XML,
            <<<XML
            <adminEmail>thirdmail@my.org</adminEmail>
            XML
        ];

        $writer = $this->getWriter();
        $xmls = $writer->writeIdentifyElements(
            'my repository',
            $this->getURI('http://www.my.org/test/repository'),
            new \DateTimeImmutable('2017-11-04', new \DateTimeZone('UTC')),
            'somemail@my.org',
            'othermail@my.org',
            'thirdmail@my.org'
        );

        $index = 0;
        foreach ($xmls as $xml) {
            if ($index < count($expected_xmls)) {
                $this->assertXmlStringEqualsXmlString($expected_xmls[$index], $xml->saveXML());
            }
            $index++;
        }
        $this->assertSame(count($expected_xmls), $index);
    }


    public function testWriteMetadataFormat(): void
    {
        $expected_xml = <<<XML
            <metadataFormat>
              <metadataPrefix>oai_dc</metadataPrefix>
              <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
              <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
            </metadataFormat>
            XML;

        $writer = $this->getWriter();
        $xml = $writer->writeMetadataFormat();

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->saveXML());
    }

    public function testWriteRecordHeader(): void
    {
        $expected_xml = <<<XML
            <header>
              <identifier>id_en:ti/fier</identifier>
              <datestamp>2013-05-30</datestamp>
            </header>
            XML;

        $writer = $this->getWriter();
        $xml = $writer->writeRecordHeader(
            'id_en:ti/fier',
            new \DateTimeImmutable('2013-05-30', new \DateTimeZone('UTC')),
        );

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->saveXML());
    }

    public function testWriteRecord(): void
    {
        $md_xml = <<<XML
            <some>
              <meta>data</meta>
              <creator importance="very" >me!</creator>
            </some>
            XML;

        $header_xml = <<<XML
            <header>
              <identifier>id_en:ti/fier</identifier>
              <datestamp>2013-05-30</datestamp>
            </header>
            XML;

        $expected_xml = '<record>' . $header_xml . '<metadata>' . $md_xml . '</metadata></record>';
        $md_doc = new \DomDocument();
        $md_doc->loadXML($md_xml);

        $writer = $this->getWriter();
        $xml = $writer->writeRecord(
            'id_en:ti/fier',
            new \DateTimeImmutable('2013-05-30', new \DateTimeZone('UTC')),
            $md_doc
        );

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->saveXML());
    }

    public function testWriteResumptionToken(): void
    {
        $expected_xml = <<<XML
            <resumptionToken completeListSize="1234" cursor="56">some token</resumptionToken>
            XML;

        $writer = $this->getWriter();
        $xml = $writer->writeResumptionToken('some token', 1234, 56);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->saveXML());
    }


    public function testWriteResponse(): void
    {
        $content1_xml = <<<XML
            <some>
              <con>tent</con>
            </some>
            XML;

        $content2_xml = <<<XML
            <different>
              <bits>and</bits>
              <bobs variety="much"/>
            </different>
            XML;

        $expected_xml_start = /** @lang text */ <<<XML
            <?xml version="1.0" encoding="UTF-8" ?>
            <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
             <responseDate>2018-08-21T02:30:35Z</responseDate>
             <request verb="GetRecord" identifier="identifier_val"
                      metadataPrefix="metadataPrefix_val">http://www.my.org/test/repository</request> 
             <GetRecord>
            XML;

        $expected_xml_end = /** @lang text */ <<<XML
             </GetRecord> 
            </OAI-PMH>
            XML;

        $expected_xml = $expected_xml_start . $content1_xml . $content2_xml . $expected_xml_end;
        $content1_doc = new \DomDocument();
        $content1_doc->loadXML($content1_xml);
        $content2_doc = new \DomDocument();
        $content2_doc->loadXML($content2_xml);
        $request = $this->getRequest(
            'http://www.my.org/test/repository',
            Verb::GET_RECORD,
            Argument::IDENTIFIER,
            Argument::MD_PREFIX
        );
        $writer = $this->getWriter('2018-08-21T02:30:35Z');
        $xml = $writer->writeResponse($request, $content1_doc, $content2_doc);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->saveXML());
    }

    public function testWriteErrorResponse(): void
    {
        $content1_xml = <<<XML
            <some>
              <con>tent</con>
            </some>
            XML;

        $content2_xml = <<<XML
            <different>
              <bits>and</bits>
              <bobs variety="much"/>
            </different>
            XML;

        $expected_xml_start = /** @lang text */ <<<XML
            <?xml version="1.0" encoding="UTF-8" ?>
            <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
             <responseDate>2018-08-21T02:30:35Z</responseDate>
             <request verb="ListIdentifiers" identifier="identifier_val"
                      metadataPrefix="metadataPrefix_val">http://www.my.org/test/repository</request>
            XML;

        $expected_xml_end = /** @lang text */ <<<XML
            </OAI-PMH>
            XML;

        $expected_xml = $expected_xml_start . $content1_xml . $content2_xml . $expected_xml_end;
        $content1_doc = new \DomDocument();
        $content1_doc->loadXML($content1_xml);
        $content2_doc = new \DomDocument();
        $content2_doc->loadXML($content2_xml);
        $request = $this->getRequest(
            'http://www.my.org/test/repository',
            Verb::LIST_IDENTIFIERS,
            Argument::IDENTIFIER,
            Argument::MD_PREFIX
        );
        $writer = $this->getWriter('2018-08-21T02:30:35Z');
        $xml = $writer->writeErrorResponse($request, $content1_doc, $content2_doc);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->saveXML());
    }



    public function testWriteErrorResponseNoVerb(): void
    {
        $content_xml = <<<XML
            <some>
              <con>tent</con>
            </some>
            XML;

        $expected_xml_start = /** @lang text */ <<<XML
            <?xml version="1.0" encoding="UTF-8" ?>
            <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
             <responseDate>2018-08-21T02:30:35Z</responseDate>
             <request identifier="identifier_val" metadataPrefix="metadataPrefix_val">http://www.my.org/test/repository</request>
            XML;

        $expected_xml_end = /** @lang text */ <<<XML
            </OAI-PMH>
            XML;

        $expected_xml = $expected_xml_start . $content_xml . $expected_xml_end;
        $content_doc = new \DomDocument();
        $content_doc->loadXML($content_xml);
        $request = $this->getRequest(
            'http://www.my.org/test/repository',
            Verb::NULL,
            Argument::IDENTIFIER,
            Argument::MD_PREFIX
        );
        $writer = $this->getWriter('2018-08-21T02:30:35Z');
        $xml = $writer->writeErrorResponse($request, $content_doc);

        $this->assertXmlStringEqualsXmlString($expected_xml, $xml->saveXML());
    }
}
