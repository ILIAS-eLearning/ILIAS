<?php

declare(strict_types=1);

namespace ILIAS\Filesystem\Util;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class PHPFunctions
 *
 * The purpose of this class is to wrap all stream handling php functions.
 *
 * This allows to mock the functions within unit test which would otherwise require us to redefine the
 * function in a scope which is scanned before the root scope and somehow call the function on our mocks the verify the
 * function calls.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class PHPStreamFunctions
{
    /**
     * ftell wrapper
     *
     * @param resource $handle
     *
     * @return bool|int
     *
     * @see ftell()
     */
    public static function ftell($handle)
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
     * @return bool|string
     * @see fread()
     */
    public static function fread($handle, int $length)
    {
        return fread($handle, $length);
    }

    /**
     * @param resource $handle
     * @return bool|string
     * @see stream_get_contents()
     */
    public static function stream_get_contents($handle, $length = -1)
    {
        return stream_get_contents($handle, $length);
    }

    /**
     * @param resource $handle
     * @return bool|int
     *
     * @see fwrite()
     */
    public static function fwrite($handle, string $string, ?int $length = null)
    {
        if (is_null($length)) {
            return fwrite($handle, $string);
        }

        return fwrite($handle, $string, $length);
    }
}
