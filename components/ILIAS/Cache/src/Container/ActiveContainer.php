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

namespace ILIAS\Cache\Container;

use ILIAS\Cache\Adaptor\Adaptor;
use ILIAS\Cache\Config;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\ByTrying;
use ILIAS\Refinery\To\Transformation\FloatTransformation;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ActiveContainer implements Container
{
    private const LOCK_UNTIL = '_lock_until';
    private const GLUE = '_|||_';
    private const STRING_PREFIX = 'string' . self::GLUE;
    private const ARRAY_PREFIX = 'array' . self::GLUE;
    private const INT_PREFIX = 'int' . self::GLUE;
    private const BOOL_PREFIX = 'bool' . self::GLUE;
    private const NULL_PREFIX = 'null' . self::GLUE;
    private const TRUE = 'true';
    private const FALSE = 'false';

    private \ILIAS\Data\Factory $data_factory;
    private $null_trafo;

    public function __construct(
        private Request $request,
        private Adaptor $adaptor,
        private Config $config
    ) {
        $this->data_factory = new \ILIAS\Data\Factory();
        // see comment in buildFinalTransformation why this is not a good solution.
        $this->null_trafo = new \ILIAS\Refinery\Custom\Transformation(function ($value) {
            return null;
        });
    }

    private function pack(mixed $value): string
    {
        if (is_string($value)) {
            return self::STRING_PREFIX . $value;
        }
        if (is_array($value)) {
            array_walk_recursive($value, function (&$item): void {
                $item = $this->pack($item);
            });

            return self::ARRAY_PREFIX . json_encode($value, JSON_THROW_ON_ERROR);
        }
        if (is_int($value)) {
            return self::INT_PREFIX . $value;
        }
        if (is_bool($value)) {
            return self::BOOL_PREFIX . ($value ? self::TRUE : self::FALSE);
        }
        if (is_null($value)) {
            return self::NULL_PREFIX;
        }

        throw new \InvalidArgumentException(
            'Only strings, integers and arrays containing those values are allowed, ' . gettype($value) . ' given.'
        );
    }


    private function unpack(?string $value): string|int|array|bool|null
    {
        if ($value === null) {
            return null;
        }
        if ($value === self::NULL_PREFIX) {
            return null;
        }
        if (str_starts_with($value, self::STRING_PREFIX)) {
            return str_replace(self::STRING_PREFIX, '', $value);
        }
        if (str_starts_with($value, self::BOOL_PREFIX)) {
            return (str_replace(self::BOOL_PREFIX, '', $value) === self::TRUE);
        }
        if (str_starts_with($value, self::ARRAY_PREFIX)) {
            $value = str_replace(self::ARRAY_PREFIX, '', $value);
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            array_walk_recursive($value, function (&$item): void {
                $item = $this->unpack($item);
            });

            return $value;
        }
        if (str_starts_with($value, self::INT_PREFIX)) {
            return (int) str_replace(self::INT_PREFIX, '', $value);
        }
        return null;
    }

    protected function buildFinalTransformation(Transformation $transformation): \ILIAS\Refinery\ByTrying
    {
        // This is a workaround for the fact that the ByTrying transformation cannot be created by
        // $DIC->refinery()->byTrying() since we are in a hell of dependencies. E.g. we cant instantiate the
        // caching service with $DIC->refinery() since the Refinery needs ilLanguage, but ilLanguage
        // needs the caching service...
        return new ByTrying([$transformation, $this->getNullFallback()], $this->data_factory);
    }

    protected function getNullFallback(): Transformation
    {
        return $this->null_trafo;
    }


    public function isLocked(): bool
    {
        // see comment in buildFinalTransformation why this is not a good solution.
        $lock_until = $this->adaptor->get($this->request->getContainerKey(), self::LOCK_UNTIL);
        $lock_until = $lock_until === null ? null : (float) $lock_until;

        return $lock_until !== null && $lock_until > microtime(true);
    }

    public function lock(float $seconds): void
    {
        if ($seconds > 300.0 || $seconds < 0.0) {
            throw new \InvalidArgumentException('Locking for more than 5 minutes is not allowed.');
        }
        $lock_until = (string) (microtime(true) + $seconds);
        $this->adaptor->set($this->request->getContainerKey(), self::LOCK_UNTIL, $lock_until, 300);
    }

    public function has(string $key): bool
    {
        if ($this->isLocked()) {
            return false;
        }

        return $this->adaptor->has($this->request->getContainerKey(), $key);
    }

    public function get(string $key, Transformation $transformation): string|int|array|bool|null
    {
        if ($this->isLocked()) {
            return null;
        }
        $unpacked_values = $this->unpack(
            $this->adaptor->get($this->request->getContainerKey(), $key)
        );

        return $this->buildFinalTransformation($transformation)->transform($unpacked_values);
    }

    public function set(string $key, string|int|array|bool|null $value): void
    {
        if ($this->isLocked()) {
            return;
        }
        $this->adaptor->set(
            $this->request->getContainerKey(),
            $key,
            $this->pack($value),
            $this->config->getDefaultTTL()
        );
    }

    public function delete(string $key): void
    {
        if ($this->isLocked()) {
            return;
        }
        $this->adaptor->delete($this->request->getContainerKey(), $key);
    }

    public function flush(): void
    {
        $this->adaptor->flushContainer($this->request->getContainerKey());
    }

    public function getAdaptorName(): string
    {
        return $this->config->getAdaptorName();
    }

    public function getContainerName(): string
    {
        return $this->request->getContainerKey();
    }
}
