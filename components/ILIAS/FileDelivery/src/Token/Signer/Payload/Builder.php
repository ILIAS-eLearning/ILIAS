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

namespace ILIAS\FileDelivery\Token\Signer\Payload;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileDelivery\Delivery\Disposition;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class Builder
{
    public function file(
        FileStream $stream,
        string $filename,
        Disposition $disposition,
        int $valid_for_at_least_hours
    ): FilePayload {
        $uri = $stream->getMetadata()['uri'];

        return new FilePayload(
            $uri,
            mime_content_type($uri),
            $filename,
            $disposition->value,
            $valid_for_at_least_hours
        );
    }

    public function fileFromRaw(
        array $raw
    ): FilePayload {
        return FilePayload::fromArray($raw);
    }
}
