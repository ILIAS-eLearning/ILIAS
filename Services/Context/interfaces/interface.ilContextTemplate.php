<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilContextTemplate
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Gabriel Comte <gc@studer-raimann.ch>
 */
interface ilContextTemplate
{
    /**
     * Are redirects supported?
     *
     * @return bool
     */
    public static function supportsRedirects();

    /**
     * Based on user authentication?
     *
     * @return bool
     */
    public static function hasUser();

    /**
     * Uses HTTP aka browser
     *
     * @return bool
     */
    public static function usesHTTP();

    /**
     * Has HTML output
     *
     * @return bool
     */
    public static function hasHTML();

    /**
     * Uses template engine
     *
     * @return bool
     */
    public static function usesTemplate();

    /**
     * Init client
     *
     * @return bool
     */
    public static function initClient();

    /**
     * Try authentication
     *
     * @return bool
     */
    public static function doAuthentication();
    
    
    /**
     * Check if persistent sessions are supported
     * false for context cli
     */
    public static function supportsPersistentSessions();

    /**
     * Check if push messages are supported, see #0018206
     * @return bool
     */
    public static function supportsPushMessages();

    /**
     * Context that are not only temporary in a session (e.g. WAC is, Cron is not)
     *
     * @return bool
     */
    public static function isSessionMainContext();
}
