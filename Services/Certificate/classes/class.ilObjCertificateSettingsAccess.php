<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * Class ilObjCertificateSettingsAccess
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 */
class ilObjCertificateSettingsAccess extends ilObjectAccess
{
    public static function hasBackgroundImage() : bool
    {
        if (is_file(self::getBackgroundImagePath()) && filesize(self::getBackgroundImagePath()) > 0) {
            return true;
        }
        return false;
    }

    public static function getBackgroundImageDefaultFolder() : string
    {
        return CLIENT_WEB_DIR . "/certificates/default/";
    }

    public static function getBackgroundImagePath(bool $asRelative = false) : string
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

    public static function getBackgroundImageName() : string
    {
        return "background.jpg";
    }

    public static function getBackgroundImageThumbPath() : string
    {
        return self::getBackgroundImageDefaultFolder() . self::getBackgroundImageName() . ".thumb.jpg";
    }

    public static function getBackgroundImageThumbPathWeb() : string
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
