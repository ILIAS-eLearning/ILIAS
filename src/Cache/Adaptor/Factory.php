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

namespace ILIAS\Cache\Adaptor;

use ILIAS\Cache\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Factory
{
    public function getSpecific(string $adaptor, Config $config): Adaptor
    {
        return match ($adaptor) {
            Config::APCU => new APCu($config),
            Config::PHPSTATIC => new PHPStatic($config),
            Config::MEMCACHED => new Memcached($config),
            default => throw new \InvalidArgumentException('Unknown Adapter'),
        };
    }

    public function getWithConfig(Config $config): Adaptor
    {
        return $this->getSpecific($config->getAdaptorName(), $config);
    }
}
