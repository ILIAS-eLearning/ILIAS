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
    private string $instance_prefix;

    public function __construct(protected Config $config)
    {
        // generates a unique prefix for the current instance. this is only to prevent collisions when running multiple
        // ILIAS Instances on the same server. It uses the md5 hash of the current working directory and takes the first
        // 6 characters. This is not a secure way to generate a prefix, but it is sufficient for this purpose.
        // It's highly unlikely that two paths will result in the same prefix.
        $this->instance_prefix = substr(md5(getcwd()), 0, 6);
    }

    protected function buildKey(string $container, string $key): string
    {
        return $this->buildContainerPrefix($container) . $key;
    }

    protected function buildContainerPrefix(string $container): string
    {
        return $this->instance_prefix . self::CONTAINER_PREFIX_SEPARATOR . $container . self::CONTAINER_PREFIX_SEPARATOR;
    }
}
