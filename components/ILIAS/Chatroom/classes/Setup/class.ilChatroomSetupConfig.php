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

use ILIAS\Setup;

class ilChatroomSetupConfig implements Setup\Config
{
    public function __construct(
        protected string $address,
        protected int $port,
        protected string $sub_directory,
        protected string $protocol,
        protected string $cert,
        protected string $key,
        protected string $dhparam,
        protected string $log,
        protected string $log_level,
        protected string $error_log,
        protected bool $ilias_proxy,
        protected string $ilias_url,
        protected bool $client_proxy,
        protected string $client_url,
        protected bool $deletion_mode,
        protected string $deletion_unit,
        protected int $deletion_value,
        protected string $deletion_time
    ) {
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getSubDirectory(): string
    {
        return $this->sub_directory;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getCert(): string
    {
        return $this->cert;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDhparam(): string
    {
        return $this->dhparam;
    }

    public function getLog(): string
    {
        return $this->log;
    }

    public function getLogLevel(): string
    {
        return $this->log_level;
    }

    public function getErrorLog(): string
    {
        return $this->error_log;
    }

    public function hasIliasProxy(): bool
    {
        return $this->ilias_proxy;
    }

    public function getIliasUrl(): string
    {
        return $this->ilias_url;
    }

    public function hasClientProxy(): bool
    {
        return $this->client_proxy;
    }

    public function getClientUrl(): string
    {
        return $this->client_url;
    }

    public function hasDeletionMode(): bool
    {
        return $this->deletion_mode;
    }

    public function getDeletionUnit(): string
    {
        return $this->deletion_unit;
    }

    public function getDeletionValue(): int
    {
        return $this->deletion_value;
    }

    public function getDeletionTime(): string
    {
        return $this->deletion_time;
    }
}
