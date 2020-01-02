<?php
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

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjCertificateSettingsAccess
*
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
*/
class ilObjCertificateSettingsAccess extends ilObjectAccess
{
    /**
    * Returns wheather or not a default background image exists
    *
    * @return boolean TRUE if a background image exists, FALSE otherwise
    */
    public static function hasBackgroundImage()
    {
        if (@file_exists(ilObjCertificateSettingsAccess::getBackgroundImagePath()) && (@filesize(ilObjCertificateSettingsAccess::getBackgroundImagePath()) > 0)) {
            return true;
        }
        return false;
    }

    /**
    * Returns the filesystem path for the default background image
    *
    * @return string The filesystem path of the background image
    */
    public static function getBackgroundImageDefaultFolder()
    {
        return CLIENT_WEB_DIR . "/certificates/default/";
    }

    /**
    * Returns the filesystem path of the background image
    * @param bool $asRelative
    * @return string The filesystem path of the background image
    */
    public static function getBackgroundImagePath($asRelative = false)
    {
        $imagePath = ilObjCertificateSettingsAccess::getBackgroundImageDefaultFolder() . ilObjCertificateSettingsAccess::getBackgroundImageName();

        if ($asRelative) {
            return str_replace(
                array(CLIENT_WEB_DIR, '//'),
                array('[CLIENT_WEB_DIR]', '/'),
                $imagePath
            );
        }

        return $imagePath;
    }

    /**
    * Returns the filename of the background image
    *
    * @return string The filename of the background image
    */
    public static function getBackgroundImageName()
    {
        return "background.jpg";
    }

    /**
    * Returns the filesystem path of the background image thumbnail
    *
    * @return string The filesystem path of the background image thumbnail
    */
    public static function getBackgroundImageThumbPath()
    {
        return ilObjCertificateSettingsAccess::getBackgroundImageDefaultFolder() . ilObjCertificateSettingsAccess::getBackgroundImageName() . ".thumb.jpg";
    }

    /**
    * Returns the web path of the background image
    *
    * @return string The web path of the background image
    */
    public static function getBackgroundImagePathWeb()
    {
        return str_replace(
            ilUtil::removeTrailingPathSeparators(
            ILIAS_ABSOLUTE_PATH
        ),
            ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            ilObjCertificateSettingsAccess::getBackgroundImagePath()
        );
    }
    
    /**
    * Returns the web path of the background image thumbnail
    *
    * @return string The web path of the background image thumbnail
    */
    public static function getBackgroundImageThumbPathWeb()
    {
        return str_replace(
            ilUtil::removeTrailingPathSeparators(
            ILIAS_ABSOLUTE_PATH
        ),
            ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            ilObjCertificateSettingsAccess::getBackgroundImageThumbPath()
        );
    }
}
