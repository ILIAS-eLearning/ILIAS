<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Service context for soap without handling authentication
 */
class ilContextSoapNoAuth implements ilContextTemplate
{

    /**
     * @inheritdoc
     */
    public static function supportsRedirects()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasUser()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function usesHTTP()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasHTML()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function usesTemplate()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function initClient()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function doAuthentication()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function supportsPersistentSessions()
    {
        return false;
    }

    /**
     * @inheritdoc
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
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function modifyHttpPath(string $httpPath) : string
    {
        return $httpPath;
    }
}
