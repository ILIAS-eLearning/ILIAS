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
    private const GLUE = '|||||';
    private const STRING_PREFIX = 'string';
    private const ARRAY_PREFIX = 'array';
    private const INT_PREFIX = 'int';
    private const BOOL_PREFIX = 'bool';
    private const NULL_PREFIX = 'null';
    private const TRUE = 'true';
    private const FALSE = 'false';

    private \ILIAS\Data\Factory $data_factory;
    private $null_trafo;
    private string $prefix_pattern;

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
        $this->prefix_pattern = '(' . implode('|', [
                preg_quote(self::STRING_PREFIX, '/'),
                preg_quote(self::ARRAY_PREFIX, '/'),
                preg_quote(self::INT_PREFIX, '/'),
                preg_quote(self::BOOL_PREFIX, '/'),
                preg_quote(self::NULL_PREFIX, '/')
            ]) . ')';
    }

    private function pack(mixed $value): string
    {
        if (is_string($value)) {
            return self::STRING_PREFIX . self::GLUE . $value;
        }
        if (is_array($value)) {
            $value = $this->packRecursive($value);

            return self::ARRAY_PREFIX . self::GLUE . json_encode($value, JSON_THROW_ON_ERROR);
        }
        if (is_int($value)) {
            return self::INT_PREFIX . self::GLUE . $value;
        }
        if (is_bool($value)) {
            return self::BOOL_PREFIX . self::GLUE . ($value ? self::TRUE : self::FALSE);
        }
        if (is_null($value)) {
            return self::NULL_PREFIX . self::GLUE;
        }

        throw new \InvalidArgumentException(
            'Only strings, integers and arrays containing those values are allowed, ' . gettype($value) . ' given.'
        );
    }

    private function packRecursive(array $value): array
    {
        array_walk($value, function (&$item): void {
            if (is_array($item)) {
                $item = $this->packRecursive($item);
            } else {
                $item = $this->pack($item);
            }
        });
        return $value;
    }

    private function unprefix(string $value): array
    {
        $str = '/^' . $this->prefix_pattern . preg_quote(self::GLUE, '/') . '(.*)/is';
        if (!preg_match($str, $value, $matches)) {
            return [self::NULL_PREFIX, null];
        }

        return [$matches[1], $matches[2]];
    }

    private function unpack(?string $value): string|int|array|bool|null
    {
        // simple detection
        if ($value === null) {
            return null;
        }
        if ($value === self::NULL_PREFIX . self::GLUE) {
            return null;
        }

        // type detection
        [$type, $unprefixed_value] = $this->unprefix($value);

        switch ($type) {
            case self::STRING_PREFIX:
                return $unprefixed_value;
            case self::BOOL_PREFIX:
                return $unprefixed_value === self::TRUE;
            case self::ARRAY_PREFIX:
                $unprefixed_value = json_decode($unprefixed_value, true, 512);
                if (!is_array($unprefixed_value)) {
                    return null;
                }

                return $this->unpackRecursive($unprefixed_value);
            case self::INT_PREFIX:
                return (int) $unprefixed_value;
            default:
                return null;
        }

        return null;
    }

    private function unpackRecursive(array $value): array
    {
        array_walk($value, function (&$item): void {
            if (is_array($item)) {
                $item = $this->unpackRecursive($item);
            } else {
                $item = $this->unpack($item);
            }
        });
        return $value;
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
