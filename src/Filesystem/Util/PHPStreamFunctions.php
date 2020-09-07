<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Util;

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
 * @since 5.3
 * @version 1.0.0
 */
final class PHPStreamFunctions
{

    /**
     * ftell wrapper
     *
     * @param $handle
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
     * fclose wrapper
     *
     * @param $handle
     *
     * @see fclose()
     */
    public static function fclose($handle)
    {
        fclose($handle);
    }


    /**
     * fseek wrapper.
     *
     * @param $stream
     * @param $offset
     * @param $whence
     *
     * @return int 0 or -1
     */
    public static function fseek($stream, $offset, $whence)
    {
        return fseek($stream, $offset, $whence);
    }


    /**
     * fread wrapper
     *
     * @param $handle
     * @param $length
     *
     * @return bool|string
     *
     * @see fread()
     */
    public static function fread($handle, $length)
    {
        return fread($handle, $length);
    }


    /**
     * stream_get_contents wrapper
     *
     * @param $handle
     * @param $length
     *
     * @return bool|string
     *
     * @see stream_get_contents()
     */
    public static function stream_get_contents($handle, $length = -1)
    {
        return stream_get_contents($handle, $length);
    }


    /**
     * fwrite wrapper
     *
     * @param      $handle
     * @param      $string
     * @param null $length
     *
     * @return bool|int
     *
     * @see fwrite()
     */
    public static function fwrite($handle, $string, $length = null)
    {

        //it seems like php juggles the null to 0 and pass it to the function which leads to a write operation of zero length ...
        if (is_null($length)) {
            return fwrite($handle, $string);
        }

        return fwrite($handle, $string, $length);
    }
}
