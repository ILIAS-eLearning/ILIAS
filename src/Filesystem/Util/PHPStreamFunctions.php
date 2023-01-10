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

namespace ILIAS\Filesystem\Util;

/**
 * The purpose of this class is to wrap all stream handling php functions.
 *
 * This allows to mock the functions within unit test which would otherwise require us to redefine the
 * function in a scope which is scanned before the root scope and somehow call the function on our mocks the verify the
 * function calls.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class PHPStreamFunctions
{
    /**
     * ftell wrapper
     *
     * @param resource $handle
     *
     *
     * @see ftell()
     */
    public static function ftell($handle): bool|int
    {
        return ftell($handle);
    }

    /**
     * @param resource $stream
     * @return int 0 or -1
     */
    public static function fseek($stream, int $offset, int $whence): int
    {
        return fseek($stream, $offset, $whence);
    }

    /**
     * @param resource $handle
     * @see fclose()
     */
    public static function fclose($handle): void
    {
        fclose($handle);
    }

    /**
     * @param resource $handle
     * @see fread()
     */
    public static function fread($handle, int $length): bool|string
    {
        return fread($handle, $length);
    }

    /**
     * @param resource $handle
     * @see stream_get_contents()
     */
    public static function stream_get_contents($handle, $length = -1): bool|string
    {
        return stream_get_contents($handle, $length);
    }

    /**
     * @param resource $handle
     *
     * @see fwrite()
     */
    public static function fwrite($handle, string $string, ?int $length = null): bool|int
    {
        if (is_null($length)) {
            return fwrite($handle, $string);
        }

        return fwrite($handle, $string, $length);
    }
}
