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

/**
* Activation Checker. Keep this class small, since it is included, even if
* DiskQuota is deactivated.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id$
*
* @ingroup webdav
*/
class ilDiskQuotaActivationChecker
{
    private static $isActive;
    private static $isReminderMailActive;
    private static $isSummaryMailActive;
    private static $isWspActive;

    /**
    * Static getter. Returns true, if disk quota is active.
    *
    * Disk quota is active if the variable "enabled"
    * is set in ilSetting('disk_quota')
    *
    * @return	boolean	value
    */
    public static function _isActive()
    {
        if (self::$isActive == null) {
            $settings = new ilSetting('disk_quota');
            self::$isActive = $settings->get('enabled') == true;
        }

        return self::$isActive;
    }
    /**
    * Static getter. Returns true, if disk quota reminder mails is active.
    *
    * Reminder mails is is active if the variable "reminder_enabled"
    * is set in ilSetting('disk_quota')
    *
    * @return	boolean	value
    */
    public static function _isReminderMailActive()
    {
        if (self::$isReminderMailActive == null) {
            $settings = new ilSetting('disk_quota');
            self::$isReminderMailActive = self::_isActive() && $settings->get('reminder_mail_enabled') == true;
        }

        return self::$isReminderMailActive;
    }
    
    /**
    * Static getter. Returns true, if disk quota summary mails are active.
    *
    * Summary mails are active if the variable "summary_mail_enabled"
    * is set in ilSetting('disk_quota')
    *
    * @return	boolean	value
    */
    public static function _isSummaryMailActive()
    {
        if (self::$isSummaryMailActive == null) {
            $settings = new ilSetting('disk_quota');
            self::$isSummaryMailActive = self::_isActive() && (int) $settings->get('summary_mail_enabled') == 1;
        }

        return self::$isSummaryMailActive;
    }
    
    /**
    * Static getter. Returns true, if personal workspace disk quota is active.
    *
    * Disk quota is active if the variable "wsp_enabled"
    * is set in ilSetting('disk_quota')
    *
    * @return	boolean	value
    */
    public static function _isPersonalWorkspaceActive()
    {
        if (self::$isWspActive == null) {
            $settings = new ilSetting('disk_quota');
            self::$isWspActive = $settings->get('wsp_enabled') == true;
        }

        return self::$isWspActive;
    }
}
