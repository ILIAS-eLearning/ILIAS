<?php


/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Context/interfaces/interface.ilContextTemplate.php";

/**
 * Service context for LTI provider
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ingroup ServicesContext
 */
class ilContextLTIProvider implements ilContextTemplate
{
    
    /**
     * Do authentication
     */
    public static function doAuthentication()
    {
        return true;
    }

    /**
     * Has html
     */
    public static function hasHTML()
    {
        return true;
    }

    /**
     * Has user (maybe?)
     */
    public static function hasUser()
    {
        return true;
    }

    /**
     * init client
     */
    public static function initClient()
    {
        return true;
    }

    /**
     * supports persistent session
     */
    public static function supportsPersistentSessions()
    {
        return true;
    }

    /**
     * supports redirects
     */
    public static function supportsRedirects()
    {
        return true;
    }

    /**
     * uses http
     */
    public static function usesHTTP()
    {
        return true;
    }

    /**
     * uses template
     */
    public static function usesTemplate()
    {
        return true;
    }

    /**
     * Supports push messages
     *
     * @return bool
     */
    public static function supportsPushMessages()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isSessionMainContext()
    {
        return false;
    }
}
