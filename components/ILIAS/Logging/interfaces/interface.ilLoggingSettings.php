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
 * @deprecated Please use {@see \ILIAS\Logging\Config\ConfigInterface} instead.
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

    public function getLevel(): int;

    public function getLevelByComponent(string $a_component_id): int;
}
