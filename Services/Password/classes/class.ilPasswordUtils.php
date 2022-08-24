<?php

declare(strict_types=1);

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

/**
 * Class ilPasswordUtils
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUser
 */
class ilPasswordUtils
{
    /**
     * Generate random bytes using OpenSSL or Mcrypt and mt_rand() as fallback
     * @return string A byte string
     */
    public static function getBytes(int $length): string
    {
        try {
            return random_bytes($length);
        } catch (Throwable $e) {
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
