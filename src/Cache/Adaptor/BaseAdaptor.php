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
abstract class BaseAdaptor implements Adaptor
{
    protected const LOCK_UNTIL = '_lock_until';

    public function __construct(protected Config $config)
    {
    }

    protected function buildKey(string $container, string $key): string
    {
        return $this->buildContainerPrefix($container) . $key;
    }

    protected function buildContainerPrefix(string $container): string
    {
        return $container . self::CONTAINER_PREFIX_SEPARATOR;
    }
}
