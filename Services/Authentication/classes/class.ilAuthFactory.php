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
 * Authentication frontend factory
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilAuthFactory
{
    /**
     * Web based authentication
     */
    public const CONTEXT_WEB = 1;

    /**
     * HTTP Auth used for WebDAV and CalDAV
     * If a special handling for WebDAV or CalDAV is required
     * overwrite ilAuthHTTP with ilAuthCalDAV and create new
     * constants.
     */
    public const CONTEXT_HTTP = 2;


    /**
     * SOAP based authentication
     */
    public const CONTEXT_SOAP = 3;

    public const CONTEXT_CAS = 5;

    /**
     * Maybe not required. HTTP based authentication for calendar access
     */
    public const CONTEXT_CALENDAR = 6;


    /**
     * Calendar authentication with auth token
     */
    public const CONTEXT_CALENDAR_TOKEN = 7;


    /**
     * Calendar authentication with auth token
     */
    public const CONTEXT_ECS = 8;



    /**
     * Apache based authentication
     */
    public const CONTEXT_APACHE = 10;

    private static int $context = self::CONTEXT_WEB;

    /**
     *
     * @return int current context
     */
    public static function getContext(): int
    {
        return self::$context;
    }

    /**
     * set context
     */
    public static function setContext(int $a_context): void
    {
        self::$context = $a_context;
    }
}
