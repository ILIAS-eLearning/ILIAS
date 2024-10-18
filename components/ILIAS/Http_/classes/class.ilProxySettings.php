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

declare(strict_types=1);

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

    public function __construct(
        protected ilSetting $setting
    ) {
        $this->read();
    }

    public static function _getInstance(): ilProxySettings
    {
        if (null === self::$_instance) {
            global $DIC;
            self::$_instance = new self($DIC->settings());
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
