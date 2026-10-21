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

namespace ILIAS\FileDelivery\Activities;

use ILIAS\Data\Description;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * Builds the payload of a delivering activity and describes its shape.
 *
 * This is the piece the delivering activities share: FileDelivery provides it,
 * every component that delivers files pulls it. That way the output of all
 * delivery activities stays identical, however different their storage is.
 */
readonly class FilePayloadFactory
{
    public function __construct(
        private DataFactory $data = new DataFactory(),
    ) {
    }

    public function fromString(string $filename, string $mime_type, string $content): FilePayload
    {
        return new FilePayload(
            $filename,
            $mime_type,
            strlen($content),
            base64_encode($content),
        );
    }

    /**
     * The stream is read completely and closed: it is handed over for exactly
     * this, and an archive delivers hundreds of files in one request.
     */
    public function fromStream(string $filename, string $mime_type, FileStream $stream): FilePayload
    {
        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            return $this->fromString($filename, $mime_type, $stream->getContents());
        } finally {
            $stream->close();
        }
    }

    /**
     * @param array<string, Description\Description> $additional_fields for an
     *        activity that hands out more than the file, e.g. what it left out
     */
    public function getDescription(
        Description\Factory $f,
        string $what,
        array $additional_fields = [],
    ): Description\Description {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument($what),
            [
                'filename' => $f->string($text->simpleDocument('Name of the file.')),
                'mime_type' => $f->string($text->simpleDocument('Mime type of the file.')),
                'size' => $f->int($text->simpleDocument('Size of the file in bytes.')),
                'content' => $f->string($text->simpleDocument('Content of the file, base64 encoded.')),
                ...$additional_fields,
            ]
        );
    }
}
