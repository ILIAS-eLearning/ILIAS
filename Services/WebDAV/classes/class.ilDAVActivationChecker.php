<?php

/**
 * Activation Checker. Keep this class small, since it is included, even if WebDav
 * is deactivated.
 */
class ilDAVActivationChecker
{
    /**
    * Static getter. Returns true, if the WebDAV server is active.
    *
    * THe WebDAV Server is active, if the variable file_access::webdav_enabled
    * is set in the client ini file. (Removed: , and if PEAR Auth_HTTP is installed).
    *
    * @return	boolean	value
    */
    public static function _isActive()
    {
        global $DIC;
        return $DIC->clientIni()->readVariable('file_access', 'webdav_enabled') == '1';
    }
}
