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
use ILIAS\Data\URI;

interface WriterInterface
{
    public function writeError(Error $error, string $message): \DOMDocument;

    public function writeIdentifyElements(
        string $repository_name,
        URI $base_url,
        \DateTimeImmutable $earliest_datestamp,
        string $first_admin_email,
        string ...$further_admin_emails
    ): \Generator;

    /**
     * Currently only oai_dc.
     */
    public function writeMetadataFormat(): \DOMDocument;

    public function writeRecordHeader(
        string $identifier,
        \DateTimeImmutable $datestamp
    ): \DOMDocument;

    /**
     * Also includes the header.
     */
    public function writeRecord(
        string $identifier,
        \DateTimeImmutable $datestamp,
        \DOMDocument $metadata
    ): \DOMDocument;

    public function writeResumptionToken(
        string $token,
        int $complete_list_size,
        int $cursor
    ): \DOMDocument;

    public function writeResponse(
        RequestInterface $request,
        \DOMDocument ...$contents
    ): \DOMDocument;

    public function writeErrorResponse(
        RequestInterface $request,
        \DOMDocument ...$errors
    ): \DOMDocument;
}
