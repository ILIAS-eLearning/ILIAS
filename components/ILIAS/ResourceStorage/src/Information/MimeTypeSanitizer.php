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

namespace ILIAS\ResourceStorage\Information;

/**
 * Validates and normalizes MIME type strings according to RFC 6838
 * restricted-name grammar for type and subtype. Rejects client-controlled
 * Content-Type values that are not plausible media types (e.g. XSS payloads).
 *
 * @author Michael Jansen <mjansen@databay.de>
 */
final class MimeTypeSanitizer
{
    private const FALLBACK = 'application/octet-stream';

    /**
     * RFC 6838 §4.2 restricted-name for type and subtype:
     * first char ALPHA/DIGIT, then up to 126 of ALPHA/DIGIT/!#$&^_.+-
     */
    private const PATTERN =
        '/^[a-z0-9][a-z0-9!#$&^_.+-]{0,126}\/[a-z0-9][a-z0-9!#$&^_.+-]{0,126}$/';

    public function sanitize(string $mime_type): string
    {
        $mime_type = strtolower(trim($mime_type));

        if (($pos = strpos($mime_type, ';')) !== false) {
            $mime_type = trim(substr($mime_type, 0, $pos));
        }

        if ($mime_type === '' || preg_match(self::PATTERN, $mime_type) !== 1) {
            return self::FALLBACK;
        }

        return $mime_type;
    }
}
