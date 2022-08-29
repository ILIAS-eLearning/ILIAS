<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesLogging
*/
interface ilLoggingSettings
{
    public function isEnabled(): bool;

    public function getLogDir(): string;

    public function getLogFile(): string;

    public function getLevel(): int;

    public function getLevelByComponent(string $a_component_id): int;

    public function getCacheLevel(): int;

    public function isCacheEnabled(): bool;

    public function isMemoryUsageEnabled(): bool;

    public function isBrowserLogEnabled(): bool;

    public function isBrowserLogEnabledForUser(string $a_login): bool;

    public function getBrowserLogUsers(): array;
}
