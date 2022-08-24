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
 * Class ilObjCertificateSettingsAccess
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 */
class ilObjCertificateSettingsAccess extends ilObjectAccess
{
    public static function hasBackgroundImage(): bool
    {
        return is_file(self::getBackgroundImagePath()) && filesize(self::getBackgroundImagePath()) > 0;
    }

    public static function getBackgroundImageDefaultFolder(): string
    {
        return CLIENT_WEB_DIR . "/certificates/default/";
    }

    public static function getBackgroundImagePath(bool $asRelative = false): string
    {
        $imagePath = self::getBackgroundImageDefaultFolder() . self::getBackgroundImageName();

        if ($asRelative) {
            return str_replace(
                [CLIENT_WEB_DIR, '//'],
                ['[CLIENT_WEB_DIR]', '/'],
                $imagePath
            );
        }

        return $imagePath;
    }

    public static function getBackgroundImageName(): string
    {
        return "background.jpg";
    }

    public static function getBackgroundImageThumbPath(): string
    {
        return self::getBackgroundImageDefaultFolder() . self::getBackgroundImageName() . ".thumb.jpg";
    }

    public static function getBackgroundImageThumbPathWeb(): string
    {
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(
                ILIAS_ABSOLUTE_PATH
            ),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            self::getBackgroundImageThumbPath()
        );
    }
}
