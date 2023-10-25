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

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\FileDelivery\Services;
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal This is an internal service, do not use it in your code.
 */
class InlineSrcBuilder implements SrcBuilder
{
    public function __construct(
        private Services $file_delivery
    ) {

    }

    public function getRevisionURL(
        Revision $revision,
        bool $signed = true
    ): string {
        if ($signed) {
            throw new \RuntimeException('InlineSrcBuilder does not support signed URLs');
        }
        $sream_resolver = $revision->maybeStreamResolver();
        if ($sream_resolver !== null) {
            $stream = $sream_resolver->getStream();
            if($sream_resolver->isInMemory()) {
                return $this->buildDataURLFromStream($stream);
            }

            $this->file_delivery->buildTokenURL(
                $stream,
                $revision->getTitle(),
                Disposition::INLINE,
                6, // FSX TODO
                1
            );
        }
        return '';
    }

    public function getFlavourURLs(
        Flavour $flavour,
        bool $signed = true
    ): \Generator {
        if ($signed) {
            throw new \RuntimeException('InlineSrcBuilder does not support signed URLs');
        }
        foreach ($flavour->getStreamResolvers() as $stream_resolver) {
            $stream = $stream_resolver->getStream();
            yield $this->buildDataURLFromStream($stream);
        }
    }

    public function buildDataURLFromStream(FileStream $stream): string
    {
        $mime_type = mime_content_type($stream->getMetadata()['uri']) ?: 'application/octet-stream';
        $base64 = base64_encode((string) $stream);
        return "data:$mime_type;base64,$base64";
    }
}
