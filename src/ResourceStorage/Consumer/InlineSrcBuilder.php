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

use ILIAS\ResourceStorage\Revision\Revision;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal This is an internal service, do not use it in your code.
 */
class InlineSrcBuilder implements SrcBuilder
{
    public function getRevisionURL(
        Revision $revision,
        bool $signed = true
    ): string {
        if ($signed) {
            throw new \RuntimeException('InlineSrcBuilder does not support signed URLs');
        }
        $token = $revision->maybeGetToken();
        if ($token !== null) {
            $stream = $token->resolveStream();
            $base64 = base64_encode((string)$stream);
            $mime = $stream->getMimeType();

            return "data:$mime;base64,$base64";
        }
        return '';
    }
}
