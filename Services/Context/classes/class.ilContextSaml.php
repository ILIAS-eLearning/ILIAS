<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

class ilContextSaml implements ilContextTemplate
{
    public static function supportsRedirects() : bool
    {
        return true;
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
        return true;
    }

    public static function usesTemplate() : bool
    {
        return true;
    }

    public static function initClient() : bool
    {
        return true;
    }

    public static function doAuthentication() : bool
    {
        return true;
    }

    public static function supportsPersistentSessions() : bool
    {
        return true;
    }

    public static function supportsPushMessages() : bool
    {
        return false;
    }

    public static function isSessionMainContext() : bool
    {
        return false;
    }

    public static function modifyHttpPath(string $httpPath) : string
    {
        if (strpos($httpPath, '/Services/Saml/lib/') !== false && strpos($httpPath, '/metadata.php') === false) {
            return substr($httpPath, 0, strpos($httpPath, '/Services/Saml/lib/'));
        }

        return $httpPath;
    }
}
