<?php
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
    public static function getBytes($length)
    {
        if (!defined('PHP_WINDOWS_VERSION_BUILD') && extension_loaded('openssl')) {
            $secure = null;
            $rand   = openssl_random_pseudo_bytes($length, $secure);
            if (false !== $rand && $secure === true) {
                return $rand;
            }
        }

        if (extension_loaded('mcrypt')) {
            // PHP bug #55169
            // @see https://bugs.php.net/bug.php?id=55169
            if (
                strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' ||
                version_compare(PHP_VERSION, '5.3.7') >= 0
            ) {
                $rand = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
                if ($rand !== false && strlen($rand) === $length) {
                    return $rand;
                }
            }
        }

        // Default random string generation
        $rand = '';
        for ($i = 0; $i < $length; $i++) {
            $rand .= chr(mt_rand(0, 255));
        }
        return $rand;
    }
}
