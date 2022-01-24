<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Service context for soap (no client)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilContextSoapWithoutClient implements ilContextTemplate
{
    public static function supportsRedirects() : bool
    {
        return false;
    }
    
    public static function hasUser() : bool
    {
        return true;
    }
    
    public static function usesHTTP() : bool
    {
        return true;
    }
    
    public static function hasHTML() : bool
    {
        return false;
    }
    
    public static function usesTemplate() : bool
    {
        return false;
    }
    
    public static function initClient() : bool
    {
        return false;
    }
    
    public static function doAuthentication() : bool
    {
        return false;
    }

    public static function supportsPersistentSessions() : bool
    {
        return false;
    }
    
    public static function supportsPushMessages() : bool
    {
        return false;
    }

    public static function isSessionMainContext() : bool
    {
        return true;
    }

    public static function modifyHttpPath(string $httpPath) : string
    {
        return $httpPath;
    }
}
