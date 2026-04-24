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

namespace ILIAS\Logging;

final class ServicesImpl implements Services
{
    public function getComponentLogger(string $component_id): \ilLogger
    {
        return \ilLoggerFactory::getLogger($component_id);
    }

    public function getRootLogger(): \ilLogger
    {
        return \ilLoggerFactory::getRootLogger();
    }

    public function getLogger(string $component_id): \ilLogger
    {
        return \ilLoggerFactory::getLogger($component_id);
    }

    public function getSettings(): \ilLoggingSettings
    {
        return \ilLoggerFactory::getInstance()->getSettings();
    }

    public function initUser(string $login): void
    {
        \ilLoggerFactory::getInstance()->initUser($login);
    }
}
