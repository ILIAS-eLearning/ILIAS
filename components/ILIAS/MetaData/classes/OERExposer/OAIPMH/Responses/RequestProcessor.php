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

use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RepositoryInterface as ExposedRecordsRepository;
use ILIAS\MetaData\OERExposer\OAIPMH\FlowControl\TokenHandlerInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\DateHelper;
use ILIAS\MetaData\Settings\SettingsInterface;

class RequestProcessor implements RequestProcessorInterface
{
    use DateHelper;

    protected WriterInterface $writer;
    protected SettingsInterface $settings;
    protected ExposedRecordsRepository $records_repository;
    protected TokenHandlerInterface $token_handler;

    protected readonly string $valid_md_prefix;
    protected readonly int $max_list_size;

    public function __construct(
        WriterInterface $writer,
        SettingsInterface $settings,
        ExposedRecordsRepository $resource_status_repository,
        TokenHandlerInterface $token_handler
    ) {
        $this->writer = $writer;
        $this->settings = $settings;
        $this->records_repository = $resource_status_repository;
        $this->token_handler = $token_handler;

        $this->valid_md_prefix = 'oai_dc';
        $this->max_list_size = 100;
    }

    public function getResponseToRequest(RequestInterface $request): \DomDocument
    {
        if ($request->verb() === Verb::NULL) {
            return $this->writer->writeErrorResponse(
                $request,
                $this->writer->writeError(
                    Error::BAD_VERB,
                    'No valid OAI-PMH verb in request.'
                )
            );
        }

        return match ($request->verb()) {
            Verb::GET_RECORD => $this->getRecord($request),
            Verb::IDENTIFY => $this->identify($request),
            Verb::LIST_IDENTIFIERS, Verb::LIST_RECORDS => $this->listRecordsOrIdentifiers($request),
            Verb::LIST_MD_FORMATS => $this->listMetadataFormats($request),
            Verb::LIST_SETS => $this->listSets($request),
            default => $this->writer->writeErrorResponse(
                $request,
                $this->writer->writeError(
                    Error::BAD_VERB,
                    'No valid OAI-PMH verb in request.'
                )
            )
        };
    }

    protected function getRecord(RequestInterface $request): \DomDocument
    {
        $errors = [];
        if (!$request->hasCorrectArguments([Argument::IDENTIFIER, Argument::MD_PREFIX], [], [])) {
            $errors[] = $this->writeBadArgumentError(
                Verb::GET_RECORD,
                ...$request->argumentKeys()
            );
        }

        if (
            $request->hasArgument(Argument::MD_PREFIX) &&
            $request->argumentValue(Argument::MD_PREFIX) !== $this->valid_md_prefix
        ) {
            $errors[] = $this->writer->writeError(
                Error::CANNOT_DISSEMINATE_FORMAT,
                'This repository only supports oai_dc as metadata format.'
            );
        }

        $record = null;
        if ($request->hasArgument(Argument::IDENTIFIER)) {
            $identifier = $request->argumentValue(Argument::IDENTIFIER);
            if (!$this->isIdentifierValid($identifier)) {
                $errors[] = $this->writer->writeError(
                    Error::ID_DOES_NOT_EXIST,
                    'Identifier "' . $identifier . '" is invalid for this repository.'
                );
            } elseif (is_null($record = $this->records_repository->getRecordByIdentifier(
                $this->removePrefixFromIdentifier($identifier)
            ))) {
                $errors[] = $this->writer->writeError(
                    Error::ID_DOES_NOT_EXIST,
                    'This repository does not have a record with identifier "' . $identifier . '".'
                );
            }
        }

        if (!empty($errors)) {
            return $this->writer->writeErrorResponse($request, ...$errors);
        }
        return $this->writer->writeResponse(
            $request,
            $this->writer->writeRecord(
                $this->settings->getOAIIdentifierPrefix() . $record->infos()->identfifier(),
                $record->infos()->datestamp(),
                $record->metadata()
            )
        );
    }

    protected function identify(RequestInterface $request): \DomDocument
    {
        if (!$request->hasCorrectArguments([], [], [])) {
            return $this->writer->writeErrorResponse(
                $request,
                $this->writeBadArgumentError(
                    Verb::IDENTIFY,
                    ...$request->argumentKeys()
                )
            );
        }

        return $this->writer->writeResponse(
            $request,
            ...$this->writer->writeIdentifyElements(
                $this->settings->getOAIRepositoryName(),
                $request->baseURL(),
                $this->records_repository->getEarliestDatestamp(),
                $this->settings->getOAIContactMail()
            )
        );
    }

    protected function listMetadataFormats(RequestInterface $request): \DomDocument
    {
        $errors = [];
        if (!$request->hasCorrectArguments([], [Argument::IDENTIFIER], [])) {
            $errors[] = $this->writeBadArgumentError(
                Verb::LIST_MD_FORMATS,
                ...$request->argumentKeys()
            );
        }

        if ($request->hasArgument(Argument::IDENTIFIER)) {
            $identifier = $request->argumentValue(Argument::IDENTIFIER);
            if (!$this->isIdentifierValid($identifier)) {
                $errors[] = $this->writer->writeError(
                    Error::ID_DOES_NOT_EXIST,
                    'Identifier "' . $identifier . '" is invalid for this repository.'
                );
            } elseif (!$this->records_repository->doesRecordWithIdentifierExist(
                $this->removePrefixFromIdentifier($identifier)
            )) {
                $errors[] = $this->writer->writeError(
                    Error::ID_DOES_NOT_EXIST,
                    'This repository does not have a record with identifier "' . $identifier . '".'
                );
            }
        }

        if (!empty($errors)) {
            return $this->writer->writeErrorResponse($request, ...$errors);
        }
        return $this->writer->writeResponse(
            $request,
            $this->writer->writeMetadataFormat()
        );
    }

    protected function listSets(RequestInterface $request): \DomDocument
    {
        $errors = [];
        if (!$request->hasCorrectArguments([], [], [Argument::RESUMPTION_TOKEN])) {
            $errors[] = $this->writeBadArgumentError(
                Verb::LIST_SETS,
                ...$request->argumentKeys()
            );
        }
        $errors[] = $this->writer->writeError(
            Error::NO_SET_HIERARCHY,
            'This repository does not support sets.'
        );

        return $this->writer->writeErrorResponse(
            $request,
            ...$errors
        );
    }

    protected function listRecordsOrIdentifiers(
        RequestInterface $request
    ): \DomDocument {
        $errors = [];
        if (!$request->hasCorrectArguments(
            [Argument::MD_PREFIX],
            [Argument::FROM_DATE, Argument::UNTIL_DATE, Argument::SET],
            [Argument::RESUMPTION_TOKEN]
        )) {
            $errors[] = $this->writeBadArgumentError(
                Verb::LIST_IDENTIFIERS,
                ...$request->argumentKeys()
            );
        }

        if ($request->hasArgument(Argument::SET)) {
            $errors[] = $this->writer->writeError(
                Error::NO_SET_HIERARCHY,
                'This repository does not support sets.'
            );
        }

        if (
            $request->hasArgument(Argument::MD_PREFIX) &&
            $request->argumentValue(Argument::MD_PREFIX) !== $this->valid_md_prefix
        ) {
            $errors[] = $this->writer->writeError(
                Error::CANNOT_DISSEMINATE_FORMAT,
                'This repository only supports oai_dc as metadata format.'
            );
        }

        $effective_request = clone $request;
        $offset = 0;
        if ($request->hasArgument(Argument::RESUMPTION_TOKEN)) {
            $token = $request->argumentValue(Argument::RESUMPTION_TOKEN);
            if (!$this->token_handler->isTokenValid($token)) {
                $errors[] = $this->writer->writeError(
                    Error::BAD_RESUMTPION_TOKEN,
                    'Invalid resumption token for this repository.'
                );
                return $this->writer->writeErrorResponse($effective_request, ...$errors);
            }
            $effective_request = $this->token_handler->appendArgumentsFromTokenToRequest($effective_request, $token);
            $offset = $this->token_handler->getOffsetFromToken($token);
        }

        $from_date = null;
        if ($effective_request->hasArgument(Argument::FROM_DATE)) {
            $from_date_string = $effective_request->argumentValue(Argument::FROM_DATE);
            if ($this->isStringValidAsDate($from_date_string)) {
                $from_date = $this->getDateFromString($from_date_string);
            } else {
                $errors[] = $this->writer->writeError(
                    Error::BAD_ARGUMENT,
                    'The date "' . $from_date_string . '" is invalid for this repository.'
                );
            }
        }
        $until_date = null;
        if ($effective_request->hasArgument(Argument::UNTIL_DATE)) {
            $until_date_string = $effective_request->argumentValue(Argument::UNTIL_DATE);
            if ($this->isStringValidAsDate($until_date_string)) {
                $until_date = $this->getDateFromString($until_date_string);
            } else {
                $errors[] = $this->writer->writeError(
                    Error::BAD_ARGUMENT,
                    'The date "' . $until_date_string . '" is invalid for this repository.'
                );
            }
        }

        $content_xmls = [];
        if ($effective_request->verb() === Verb::LIST_IDENTIFIERS) {
            $record_infos = $this->records_repository->getRecordInfos(
                $from_date,
                $until_date,
                $this->max_list_size,
                $offset
            );
            foreach ($record_infos as $info) {
                $content_xmls[] = $this->writer->writeRecordHeader(
                    $this->settings->getOAIIdentifierPrefix() . $info->identfifier(),
                    $info->datestamp()
                );
            }
        } elseif ($effective_request->verb() === Verb::LIST_RECORDS) {
            $records = $this->records_repository->getRecords(
                $from_date,
                $until_date,
                $this->max_list_size,
                $offset
            );
            foreach ($records as $record) {
                $content_xmls[] = $this->writer->writeRecord(
                    $this->settings->getOAIIdentifierPrefix() . $record->infos()->identfifier(),
                    $record->infos()->datestamp(),
                    $record->metadata()
                );
            }
        } else {
            throw new \ilMDOERExposerException('Invalid verb handling.');
        }

        if (empty($content_xmls)) {
            $errors[] = $this->writer->writeError(
                Error::NO_RECORDS_MATCH,
                'No matching records found.'
            );
        }

        $count = $this->records_repository->getRecordCount($from_date, $until_date);
        if (
            $request->hasArgument(Argument::RESUMPTION_TOKEN) ||
            $this->max_list_size < $count
        ) {
            $new_token = '';
            if ($offset + $this->max_list_size < $count) {
                $new_token = $this->token_handler->generateToken(
                    $offset + $this->max_list_size,
                    $from_date,
                    $until_date
                );
            }
            $content_xmls[] = $this->writer->writeResumptionToken(
                $new_token,
                $count,
                $offset
            );
        }

        if (!empty($errors)) {
            return $this->writer->writeErrorResponse($request, ...$errors);
        }
        return $this->writer->writeResponse(
            $request,
            ...$content_xmls
        );
    }

    protected function writeBadArgumentError(Verb $verb, Argument ...$arguments): \DomDocument
    {
        if (empty($arguments)) {
            $message = $verb->value . ' must come with additional arguments.';
        } else {
            $arg_strings = [];
            foreach ($arguments as $argument) {
                $arg_strings[] = $argument->value;
            }
            $message = implode(', ', $arg_strings) .
            ' is not a valid set of arguments for ' . $verb->value . '.';
        }
        return $this->writer->writeError(
            Error::BAD_ARGUMENT,
            $message
        );
    }

    protected function isIdentifierValid(string $identifier): bool
    {
        return str_starts_with($identifier, $this->settings->getOAIIdentifierPrefix()) &&
            substr($identifier, strlen($this->settings->getOAIIdentifierPrefix())) !== '';
    }

    protected function removePrefixFromIdentifier(string $identifier): string
    {
        if (str_starts_with($identifier, $this->settings->getOAIIdentifierPrefix())) {
            $identifier = substr($identifier, strlen($this->settings->getOAIIdentifierPrefix()));
        }
        return $identifier;
    }
}
