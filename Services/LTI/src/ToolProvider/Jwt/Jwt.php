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

namespace ILIAS\LTI\ToolProvider\Jwt;

use ILIAS\LTI\ToolProvider\Util;

/**
 * Class to represent an HTTP message request
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Jwt
{

    /**
     * Life (in seconds) of an issued JWT (default is 1 minute).
     *
     * @var int $life
     */
    public static int $life = 60;

    /**
     * Leeway (in seconds) to allow when checking timestamps (default is 3 minutes).
     *
     * @var int $leeway
     */
    public static int $leeway = 180;

    /**
     * Allow use of jku header in JWT.
     *
     * @var bool $allowJkuHeader
     */
    public static bool $allowJkuHeader = false;

    /**
     * The client used to handle JWTs.
     *
     * @var ClientInterface $jwtClient
     */
    private static ClientInterface $jwtClient;

    /**
     * Class constructor.
     */
    public function __construct()
    {
    }

    /**
     * Set the JWT client to use for handling JWTs.
     *
     * @param \ILIAS\LTI\ToolProvider\Jwt\ClientInterface|null $jwtClient
     *
     * @return void
     */
    public static function setJwtClient(\ILIAS\LTI\ToolProvider\Jwt\ClientInterface $jwtClient = null)
    {
        self::$jwtClient = $jwtClient;
        Util::logDebug('JwtClient set to \'' . get_class(self::$jwtClient) . '\'');
    }

    /**
     * Get the JWT client to use for handling JWTs. If one is not set, a default client is created.
     *
     * @return ClientInterface|null  The JWT client
     */
    public static function getJwtClient()
    {
        if (!self::$jwtClient) {
            self::$jwtClient = new FirebaseClient();
            Util::logDebug('JwtClient set to \'' . get_class(self::$jwtClient) . '\'');
        }

        return self::$jwtClient;
    }
}
