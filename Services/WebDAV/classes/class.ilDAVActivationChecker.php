<?php declare(strict_types = 1);
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
