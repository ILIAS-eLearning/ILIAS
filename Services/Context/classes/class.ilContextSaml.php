<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Context/interfaces/interface.ilContextTemplate.php';

/**
 * Class ilContextSaml
 */
class ilContextSaml implements ilContextTemplate
{
    /**
     * @inheritdoc
     */
    public static function supportsRedirects()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasUser()
    {
        return true;
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
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function usesTemplate()
    {
        return true;
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
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function supportsPersistentSessions()
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
