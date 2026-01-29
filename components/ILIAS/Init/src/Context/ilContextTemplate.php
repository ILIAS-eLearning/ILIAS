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

/**
 * Interface ilContextTemplate
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Gabriel Comte <gc@studer-raimann.ch>
 */
interface ilContextTemplate
{
    /**
     * Are redirects supported?
     */
    public static function supportsRedirects(): bool;

    /**
     * Based on user authentication?
     */
    public static function hasUser(): bool;

    /**
     * Uses HTTP aka browser
     */
    public static function usesHTTP(): bool;

    /**
     * Has HTML output
     */
    public static function hasHTML(): bool;

    /**
     * Uses template engine
     */
    public static function usesTemplate(): bool;

    /**
     * Init client
     */
    public static function initClient(): bool;

    /**
     * Try authentication
     */
    public static function doAuthentication(): bool;

    /**
     * Check if persistent sessions are supported
     * false for context cli
     */
    public static function supportsPersistentSessions(): bool;

    /**
     * Check if push messages are supported, see #0018206
     */
    public static function supportsPushMessages(): bool;

    /**
     * Context that are not only temporary in a session (e.g. WAC is, Cron is not)
     */
    public static function isSessionMainContext(): bool;

    /**
     * A context might modify the ILIAS http path
     * @see \ilInitialisation::buildHTTPPath
     */
    public static function modifyHttpPath(string $httpPath): string;
}
