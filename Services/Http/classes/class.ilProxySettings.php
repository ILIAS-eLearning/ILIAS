<?php

declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * class ilProxySettings
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilProxySettings
{
    protected static ?ilProxySettings $_instance = null;
    protected string $host = '';
    protected int $port = 80;
    protected bool $active = false;
    protected ilSetting $setting;

    protected function __construct()
    {
        global $DIC;

        $this->setting = $DIC->settings();
        $this->read();
    }

    public static function _getInstance(): ilProxySettings
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function read(): void
    {
        $this->host = (string) $this->setting->get('proxy_host');
        $this->port = (int) $this->setting->get('proxy_port');
        $this->active = (bool) $this->setting->get('proxy_status');
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }
}
