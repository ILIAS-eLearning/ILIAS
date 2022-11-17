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
use ILIAS\ResourceStorage\Flavour\Streams\FlavourStream;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class InlineSrcBuilder implements SrcBuilder
{
    public function getResourceURL(
        Revision $revision,
        StorageHandler $handler,
        bool $signed = true
    ): string {
        $stream = $handler->getStream($revision);
        $base64 = base64_encode($stream->getContents());
        $mime = $revision->getInformation()->getMimeType();

        return "data:$mime;base64,$base64";
    }

    public function getFlavourURLs(Flavour $flavour, bool $signed = true): \Generator
    {
        /** @var $stream \ILIAS\ResourceStorage\Flavour\Streams\FlavourStream */
        foreach ($flavour->getStreams() as $stream) {
            $base64 = base64_encode((string) $stream);
            $mime = $stream->getMimeType();

            yield "data:$mime;base64,$base64";
        }
    }
}
