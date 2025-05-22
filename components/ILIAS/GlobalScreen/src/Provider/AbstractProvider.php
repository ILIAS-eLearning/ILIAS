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

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Services;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractProvider implements Provider
{
    private string $provider_name_cache = "";

    public function __construct(protected Container $dic)
    {
    }


    protected function globalScreen(): Services
    {
        return $this->dic->globalScreen();
    }


    final public function getFullyQualifiedClassName(): string
    {
        return self::class;
    }

    /**
     * @throws \ReflectionException
     */
    public function getProviderNameForPresentation(): string
    {
        if ($this->provider_name_cache !== "" && is_string($this->provider_name_cache)) {
            return $this->provider_name_cache;
        }

        $reflector = new \ReflectionClass($this);

        $dirname = dirname($reflector->getFileName());
        $after_components = substr($dirname, strpos($dirname, '/components/') + strlen('/components/'));
        $parts = explode(
            DIRECTORY_SEPARATOR,
            $after_components
        );

        $parts = array_filter($parts, static function ($part): bool {
            $ignore = ['GlobalScreen', 'Provider', 'classes', 'GS'];
            return $part !== '' && !in_array($part, $ignore, true);
        });

        return $this->provider_name_cache = implode('/', $parts);
    }
}
