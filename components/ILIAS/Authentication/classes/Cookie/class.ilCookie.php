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
 * Representation of an HTTP cookie
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCookie
{
    private string $name;
    private string $value = '';
    private int $expire = 0;
    private string $path = '';
    private string $domain = '';
    private bool $secure = false;
    private bool $http_only = false;

    public function __construct(string $a_name)
    {
        $this->name = $a_name;
    }

    public function setName(string $a_name): void
    {
        $this->name = $a_name;
    }

    /**
     * Get name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Currently no restriction on cookie length.
     * RFC 2965 suggests a minimum of 4096 bytes
     */
    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setExpire(int $a_expire): void
    {
        $this->expire = $a_expire;
    }

    public function getExpire(): int
    {
        return $this->expire;
    }

    public function setPath(string $a_path): void
    {
        $this->path = $a_path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setDomain(string $a_domain): void
    {
        $this->domain = $a_domain;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setSecure(bool $a_status): void
    {
        $this->secure = $a_status;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function setHttpOnly(bool $a_http_only): void
    {
        $this->http_only = $a_http_only;
    }

    public function isHttpOnly(): bool
    {
        return $this->http_only;
    }
}
