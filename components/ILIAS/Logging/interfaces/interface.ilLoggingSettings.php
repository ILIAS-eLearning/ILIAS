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
