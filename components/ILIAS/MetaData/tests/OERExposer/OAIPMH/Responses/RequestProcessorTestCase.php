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
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\NullRequest;
use ILIAS\MetaData\Settings\NullSettings;
use ILIAS\MetaData\OERHarvester\ExposedRecords\NullRepository;
use ILIAS\MetaData\OERExposer\OAIPMH\FlowControl\NullTokenHandler;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RepositoryInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\FlowControl\TokenHandlerInterface;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RecordInterface;
use ILIAS\MetaData\OERHarvester\ExposedRecords\NullRecord;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RecordInfosInterface;
use ILIAS\MetaData\OERHarvester\ExposedRecords\NullRecordInfos;

abstract class RequestProcessorTestCase extends TestCase
{
    protected function getDate(string $string): \DateTimeImmutable
    {
        return new \DateTimeImmutable($string, new \DateTimeZone('UTC'));
    }

    protected function getURI(string $string): URI
    {
        $url = $this->createMock(URI::class);
        $url->method('__toString')->willReturn($string);
        return $url;
    }

    /**
     * Argument names are keys, their values values (all as strings)
     */
    protected function getRequest(
        string $base_url,
        Verb $verb,
        array $arguments_with_values,
        bool $correct_arguments = true
    ): RequestInterface {
        $base_url = $this->getURI($base_url);

        return new class ($base_url, $verb, $arguments_with_values, $correct_arguments) extends NullRequest {
            public function __construct(
                protected URI $base_url,
                protected Verb $verb,
                protected array $arguments_with_values,
                protected bool $correct_values
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
                foreach ($this->arguments_with_values as $argument_key => $argument_value) {
                    if (!is_null($r = Argument::tryFrom($argument_key))) {
                        yield $r;
                    }
                }
            }

            public function hasArgument(Argument $argument): bool
            {
                return in_array($argument->value, array_keys($this->arguments_with_values));
            }

            public function argumentValue(Argument $argument): string
            {
                return $this->arguments_with_values[$argument->value] ?? '';
            }

            public function hasCorrectArguments(
                array $required,
                array $optional,
                array $exclusive
            ): bool {
                return $this->correct_values;
            }
        };
    }

    protected function getWriter(): WriterInterface
    {
        return new class () extends NullWriter {
            public function writeError(Error $error, string $message): \DOMDocument
            {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement('error', $error->value));
                return $doc;
            }

            public function writeIdentifyElements(
                string $repository_name,
                URI $base_url,
                \DateTimeImmutable $earliest_datestamp,
                string $first_admin_email,
                string ...$further_admin_emails
            ): \Generator {
                $els = [
                    $repository_name,
                    (string) $base_url,
                    $earliest_datestamp->format('Y-m-d'),
                    $first_admin_email
                ];
                foreach ($els as $idx => $el) {
                    $doc = new \DOMDocument();
                    $doc->appendChild($doc->createElement('info', $el));
                    yield $doc;
                }
            }

            /**
             * Currently only oai_dc.
             */
            public function writeMetadataFormat(): \DOMDocument
            {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement('md_format', 'some metadata'));
                return $doc;
            }

            public function writeRecordHeader(
                string $identifier,
                \DateTimeImmutable $datestamp
            ): \DOMDocument {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement(
                    'header',
                    $identifier . ':' . $datestamp->format('Y-m-d')
                ));
                return $doc;
            }

            /**
             * Also includes the header.
             */
            public function writeRecord(
                string $identifier,
                \DateTimeImmutable $datestamp,
                \DOMDocument $metadata
            ): \DOMDocument {
                $doc = new \DOMDocument();
                $doc->appendChild($root = $doc->createElement('record'));
                $root->appendChild(
                    $doc->createElement(
                        'record_info',
                        $identifier . ':' . $datestamp->format('Y-m-d')
                    )
                );
                $root->appendChild($doc->importNode($metadata->documentElement, true));
                return $doc;
            }

            public function writeSet(string $spec, string $name): \DOMDocument
            {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement('set', $spec . ':' . $name));
                return $doc;
            }

            public function writeResumptionToken(
                string $token,
                int $complete_list_size,
                int $cursor
            ): \DOMDocument {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement(
                    'token',
                    $token . ',fullsize=' . $complete_list_size . ',cursor=' . $cursor
                ));
                return $doc;
            }

            public function writeResponse(
                RequestInterface $request,
                \DOMDocument ...$contents
            ): \DOMDocument {
                return $this->writeResponseOrErrorResponse(
                    'response',
                    $request,
                    ...$contents
                );
            }

            public function writeErrorResponse(
                RequestInterface $request,
                \DOMDocument ...$errors
            ): \DOMDocument {
                return $this->writeResponseOrErrorResponse(
                    'error_response',
                    $request,
                    ...$errors
                );
            }

            protected function writeResponseOrErrorResponse(
                string $root_name,
                RequestInterface $request,
                \DOMDocument ...$contents
            ): \DOMDocument {
                $args = [];
                foreach ($request->argumentKeys() as $key) {
                    $args[] = $key->value . '=' . $request->argumentValue($key);
                }

                $doc = new \DOMDocument();
                $doc->appendChild($root = $doc->createElement($root_name));
                $root->appendChild($doc->createElement(
                    'response_info',
                    $request->baseURL() . ':' . $request->verb()->value . ':' . implode(',', $args)
                ));
                foreach ($contents as $content) {
                    $root->appendChild($doc->importNode($content->documentElement, true));
                }
                return $doc;
            }
        };
    }

    protected function getSettings(
        string $prefix = '',
        string $repo_name = '',
        string $contact_mail = ''
    ): SettingsInterface {
        return new class ($prefix, $repo_name, $contact_mail) extends NullSettings {
            public function __construct(
                protected string $prefix,
                protected string $repo_name,
                protected string $contact_mail
            ) {
            }

            public function getOAIIdentifierPrefix(): string
            {
                return $this->prefix;
            }

            public function getOAIContactMail(): string
            {
                return $this->contact_mail;
            }

            public function getOAIRepositoryName(): string
            {
                return $this->repo_name;
            }
        };
    }

    /**
     * Append datestamps to identifiers with +YYYY-MM-DD
     */
    protected function getRepository(
        ?string $earliest_datestamp = null,
        int $record_count = 0,
        string ...$identifiers
    ): RepositoryInterface {
        $earliest_datestamp = $this->getDate($earliest_datestamp ?? '@0');
        $records = [];
        foreach ($identifiers as $identifier) {
            $records[$identifier] = new class ($identifier) extends NullRecord {
                public function __construct(protected string $identifier)
                {
                }

                public function infos(): RecordInfosInterface
                {
                    return new class ($this->identifier) extends NullRecordInfos {
                        public function __construct(protected string $identifier)
                        {
                        }

                        public function datestamp(): \DateTimeImmutable
                        {
                            return new \DateTimeImmutable(
                                explode('+', $this->identifier)[1],
                                new \DateTimeZone('UTC')
                            );
                        }

                        public function identfifier(): string
                        {
                            return $this->identifier;
                        }
                    };
                }

                public function metadata(): \DOMDocument
                {
                    $doc = new \DOMDocument();
                    $doc->appendChild($doc->createElement('md', 'md for ' . $this->identifier));
                    return $doc;
                }
            };
        }

        return new class ($earliest_datestamp, $record_count, $records) extends NullRepository {
            public array $exposed_parameters = [];

            public function __construct(
                protected \DateTimeImmutable $earliest_datestamp,
                protected int $record_count,
                protected array $records
            ) {
            }

            public function getEarliestDatestamp(): \DateTimeImmutable
            {
                return $this->earliest_datestamp;
            }

            public function doesRecordWithIdentifierExist(string $identifier): bool
            {
                return in_array($identifier, array_keys($this->records));
            }

            public function getRecordByIdentifier(string $identifier): ?RecordInterface
            {
                return $this->records[$identifier] ?? null;
            }

            public function getRecords(
                ?\DateTimeImmutable $from = null,
                ?\DateTimeImmutable $until = null,
                ?int $limit = null,
                ?int $offset = null
            ): \Generator {
                $this->exposed_parameters[] = [
                    'from' => $from?->format('Y-m-d'),
                    'until' => $until?->format('Y-m-d'),
                    'limit' => $limit,
                    'offset' => $offset,
                ];
                yield from $this->records;
            }

            public function getRecordInfos(
                ?\DateTimeImmutable $from = null,
                ?\DateTimeImmutable $until = null,
                ?int $limit = null,
                ?int $offset = null
            ): \Generator {
                $this->exposed_parameters[] = [
                    'from' => $from?->format('Y-m-d'),
                    'until' => $until?->format('Y-m-d'),
                    'limit' => $limit,
                    'offset' => $offset,
                ];
                foreach ($this->records as $record) {
                    yield $record->infos();
                }
            }

            public function getRecordCount(
                ?\DateTimeImmutable $from = null,
                ?\DateTimeImmutable $until = null
            ): int {
                return $this->record_count;
            }
        };
    }

    protected function getTokenHandler(
        bool $valid_token = true,
        ?RequestInterface $appended_request = null
    ): TokenHandlerInterface {
        return new class ($valid_token, $appended_request) extends NullTokenHandler {
            public function __construct(
                protected bool $valid_token,
                protected ?RequestInterface $appended_request
            ) {
            }

            public function generateToken(
                int $offset,
                ?\DateTimeImmutable $from_date,
                ?\DateTimeImmutable $until_date
            ): string {
                return 'next_offset=' . $offset . ':' .
                    'from=' . $from_date?->format('Y-m-d') . ':' .
                    'until=' . $until_date?->format('Y-m-d');
            }

            public function isTokenValid(string $token): bool
            {
                return $this->valid_token;
            }

            public function appendArgumentsFromTokenToRequest(
                RequestInterface $request,
                string $token
            ): RequestInterface {
                return $this->appended_request;
            }

            public function getOffsetFromToken(string $token): int
            {
                return (int) str_replace('next_offset=', '', explode(':', $token)[0]);
            }
        };
    }
}
