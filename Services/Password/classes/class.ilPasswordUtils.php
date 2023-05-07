<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPasswordUtils
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUser
 */
class ilPasswordUtils
{
    /**
     * Generate random bytes using OpenSSL or Mcrypt and mt_rand() as fallback
     * @param int $length
     * @return string A byte string
     */
    public static function getBytes(int $length) : string
    {
        try {
            return random_bytes($length);
        } catch (Throwable $ex) {
            if (!defined('PHP_WINDOWS_VERSION_BUILD') && extension_loaded('openssl')) {
                $secure = null;
                $rand = openssl_random_pseudo_bytes($length, $secure);
                if (false !== $rand && $secure === true) {
                    return $rand;
                }
            }

            $rand = '';
            for ($i = 0; $i < $length; ++$i) {
                $rand .= chr(random_int(0, 255));
            }

            return $rand;
        }
    }
}
