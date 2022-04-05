<?php declare(strict_types = 1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Activation Checker. Keep this class small, since it is included, even if WebDav
 * is deactivated.
 */
class ilDAVActivationChecker
{
    public static function _isActive()
    {
        $settings = new ilSetting('webdav');
        return $settings->get('webdav_enabled', '0') == '1';
    }
}
