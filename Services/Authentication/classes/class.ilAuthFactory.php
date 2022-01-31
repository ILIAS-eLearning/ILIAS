<?php declare(strict_types=1);

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
 * Authentication frontend factory
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesAuthentication
 */
class ilAuthFactory
{
    /**
     * @var int
     * Web based authentication
     */
    const CONTEXT_WEB = 1;

    /**
     * @var int
     * HTTP Auth used for WebDAV and CalDAV
     * If a special handling for WebDAV or CalDAV is required
     * overwrite ilAuthHTTP with ilAuthCalDAV and create new
     * constants.
     */
    const CONTEXT_HTTP = 2;
    
    
    /**
     * @var int
     * SOAP based authentication
     */
    const CONTEXT_SOAP = 3;

    /**
     * @var int
     */
    const CONTEXT_CAS = 5;
    
    /**
     * @var int
     * Maybe not required. HTTP based authentication for calendar access
     */
    const CONTEXT_CALENDAR = 6;
    
    
    /**
     * @var int
     * Calendar authentication with auth token
     */
    const CONTEXT_CALENDAR_TOKEN = 7;
    
    
    /**
     * @var int
     * Calendar authentication with auth token
     */
    const CONTEXT_ECS = 8;
    
    

    /**
     * @var int
     * Apache based authentication
     */
    const CONTEXT_APACHE = 10;

    /**
     * @var int
     */
    private static $context = self::CONTEXT_WEB;

    /**
     *
     * @return int current context
     */
    public static function getContext()
    {
        return self::$context;
    }
    
    /**
     * set context
     * @param int $a_context
     * @return
     */
    public static function setContext($a_context)
    {
        self::$context = $a_context;
    }
}
