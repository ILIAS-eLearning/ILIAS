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
 * Service context for LTI provider
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilContextLTIProvider implements ilContextTemplate
{
    public static function doAuthentication(): bool
    {
        return true;
    }

    public static function hasHTML(): bool
    {
        return true;
    }

    public static function hasUser(): bool
    {
        return true;
    }

    public static function initClient(): bool
    {
        return true;
    }

    public static function supportsPersistentSessions(): bool
    {
        return true;
    }

    public static function supportsRedirects(): bool
    {
        return true;
    }

    public static function usesHTTP(): bool
    {
        return true;
    }

    public static function usesTemplate(): bool
    {
        return true;
    }

    public static function supportsPushMessages(): bool
    {
        return false;
    }

    public static function isSessionMainContext(): bool
    {
        return false;
    }

    public static function modifyHttpPath(string $httpPath): string
    {
        return $httpPath;
    }
}
