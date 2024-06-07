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

use ILIAS\MetaData\OERExposer\OAIPMH\Responses\Error;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;
use ILIAS\Data\URI;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\DateHelper;

class Writer implements WriterInterface
{
    use DateHelper;

    public function writeError(Error $error, string $message): \DOMDocument
    {
        $xml = new \DomDocument('1.0', 'UTF-8');
        $element = $xml->createElement(
            'error',
            $message
        );
        $element->setAttribute('code', $error->value);
        $xml->appendChild($element);
        return $xml;
    }

    public function writeIdentifyElements(
        string $repository_name,
        URI $base_url,
        \DateTimeImmutable $earliest_datestamp,
        string $first_admin_email,
        string ...$further_admin_emails
    ): \Generator {
        yield $this->writeSingleElementXML(
            'repositoryName',
            $repository_name
        );
        yield $this->writeSingleElementXML(
            'baseURL',
            (string) $base_url
        );
        yield $this->writeSingleElementXML(
            'protocolVersion',
            '2.0'
        );
        yield $this->writeSingleElementXML(
            'earliestDatestamp',
            $this->getFormattedDate($earliest_datestamp)
        );
        yield $this->writeSingleElementXML(
            'deletedRecord',
            'no'
        );
        yield $this->writeSingleElementXML(
            'granularity',
            'YYYY-MM-DD'
        );

        array_unshift($further_admin_emails, $first_admin_email);
        foreach ($further_admin_emails as $admin_email) {
            yield $this->writeSingleElementXML(
                'adminEmail',
                $admin_email
            );
        }
    }

    protected function writeSingleElementXML(
        string $element_name,
        string $element_value
    ): \DOMDocument {
        $xml = new \DomDocument('1.0', 'UTF-8');
        $element_xml = $xml->createElement(
            $element_name,
            $element_value
        );
        $xml->appendChild($element_xml);
        return $xml;
    }

    public function writeMetadataFormat(): \DOMDocument
    {
        $xml = new \DomDocument('1.0', 'UTF-8');

        $root = $xml->createElement('metadataFormat');
        $xml->appendChild($root);

        $prefix = $xml->createElement(
            'metadataPrefix',
            'oai_dc'
        );
        $root->appendChild($prefix);

        $schema = $xml->createElement(
            'schema',
            'http://www.openarchives.org/OAI/2.0/oai_dc.xsd'
        );
        $root->appendChild($schema);

        $namespace = $xml->createElement(
            'metadataNamespace',
            'http://www.openarchives.org/OAI/2.0/oai_dc/'
        );
        $root->appendChild($namespace);

        return $xml;
    }

    public function writeRecordHeader(
        string $identifier,
        \DateTimeImmutable $datestamp
    ): \DOMDocument {
        $xml = new \DomDocument('1.0', 'UTF-8');

        $root = $xml->createElement('header');
        $xml->appendChild($root);

        $identifier = $xml->createElement(
            'identifier',
            $identifier
        );
        $root->appendChild($identifier);

        $datestamp = $xml->createElement(
            'datestamp',
            $this->getFormattedDate($datestamp)
        );
        $root->appendChild($datestamp);

        return $xml;
    }

    public function writeRecord(
        string $identifier,
        \DateTimeImmutable $datestamp,
        \DOMDocument $metadata
    ): \DOMDocument {
        $xml = new \DomDocument('1.0', 'UTF-8');

        $root = $xml->createElement('record');
        $xml->appendChild($root);

        $header_xml = $this->writeRecordHeader($identifier, $datestamp);
        $root->appendChild($xml->importNode($header_xml->documentElement, true));

        $metadata_xml = $xml->createElement('metadata');
        $root->appendChild($metadata_xml);
        $metadata_xml->appendChild($xml->importNode($metadata->documentElement, true));

        return $xml;
    }

    public function writeResumptionToken(
        string $token,
        int $complete_list_size,
        int $cursor
    ): \DOMDocument {
        $xml = new \DomDocument('1.0', 'UTF-8');
        $element = $xml->createElement(
            'resumptionToken',
            $token
        );
        $element->setAttribute('completeListSize', (string) $complete_list_size);
        $element->setAttribute('cursor', (string) $cursor);
        $xml->appendChild($element);
        return $xml;
    }

    public function writeResponse(
        RequestInterface $request,
        \DOMDocument ...$contents
    ): \DOMDocument {
        $xml = new \DomDocument('1.0', 'UTF-8');

        $root = $this->createRootElement($xml);
        $xml->appendChild($root);

        $date_xml = $this->createDateElement($xml);
        $root->appendChild($date_xml);
        $request_xml = $this->createRequestElement($xml, $request);
        $root->appendChild($request_xml);

        $verb_xml = $xml->createElement($request->verb()->value);
        $root->appendChild($verb_xml);

        foreach ($contents as $content_xml) {
            $verb_xml->appendChild(
                $xml->importNode($content_xml->documentElement, true)
            );
        }

        return $xml;
    }

    public function writeErrorResponse(
        RequestInterface $request,
        \DOMDocument ...$errors
    ): \DOMDocument {
        $xml = new \DomDocument('1.0', 'UTF-8');

        $root = $this->createRootElement($xml);
        $xml->appendChild($root);

        $date_xml = $this->createDateElement($xml);
        $root->appendChild($date_xml);
        $request_xml = $this->createRequestElement($xml, $request);
        $root->appendChild($request_xml);

        foreach ($errors as $error_xml) {
            $root->appendChild(
                $xml->importNode($error_xml->documentElement, true)
            );
        }

        return $xml;
    }

    protected function createRootElement(\DOMDocument $xml): \DOMElement
    {
        $root = $xml->createElement('OAI-PMH');
        $root->setAttribute(
            'xmlns',
            'http://www.openarchives.org/OAI/2.0/'
        );
        $root->setAttribute(
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $root->setAttribute(
            'xsi:schemaLocation',
            'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'
        );
        return $root;
    }

    protected function createDateElement(\DOMDocument $xml): \DOMElement
    {
        return $xml->createElement(
            'responseDate',
            $this->getFormattedDateTime($this->getCurrentDateTime())
        );
    }

    protected function createRequestElement(
        \DOMDocument $xml,
        RequestInterface $request
    ): \DOMElement {
        $request_xml = $xml->createElement(
            'request',
            (string) $request->baseURL()
        );
        if ($request->verb() !== Verb::NULL) {
            $request_xml->setAttribute(
                Argument::VERB->value,
                $request->verb()->value
            );
        }
        foreach ($request->argumentKeys() as $key) {
            $request_xml->setAttribute(
                $key->value,
                $request->argumentValue($key)
            );
        }
        return $request_xml;
    }
}
