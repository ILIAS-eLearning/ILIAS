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

namespace ILIAS\FileDelivery\Delivery;

/**
 * Builds the Content-Disposition header value per RFC 6266 / RFC 5987.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ContentDispositionHeader
{
    public function build(string $disposition, string $filename): string
    {
        $filename = str_replace(['/', '\\', "\r", "\n", "\0", '"'], '', $filename);
        if ($filename === '') {
            $filename = 'file';
        }

        // RFC 6266 fallback: printable ASCII only, no "%" a client could re-decode.
        $ascii_fallback = preg_replace('/[^\x20-\x7e]/', '_', $filename);
        $ascii_fallback = str_replace('%', '_', (string) $ascii_fallback);

        // RFC 5987 extended parameter carrying the exact name.
        return $disposition
            . '; filename="' . $ascii_fallback . '"'
            . '; filename*=UTF-8\'\'' . rawurlencode($filename);
    }
}
