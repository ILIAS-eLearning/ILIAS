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

class ilArgon2idPasswordEncoder extends ilBasePasswordEncoder
{
    private const CONFIG_KEY_TIME_COST = 'time_cost';
    private const CONFIG_KEY_MEMORY_COST = 'memory_cost';
    private const CONFIG_KEY_THREADS = 'threads';

    private ?int $memory_cost = null;
    private ?int $time_cost = null;
    private ?int $threads = null;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                switch (strtolower($key)) {
                    case self::CONFIG_KEY_MEMORY_COST:
                        $this->setMemoryCost($value);
                        break;

                    case self::CONFIG_KEY_TIME_COST:
                        $this->setTimeCost($value);
                        break;

                    case self::CONFIG_KEY_THREADS:
                        $this->setThreads($value);
                        break;
                }
            }
        }

        if ($this->isSupportedByRuntime() && static::class == self::class) {
            if (!isset($config[self::CONFIG_KEY_MEMORY_COST])) {
                $this->setMemoryCost(PASSWORD_ARGON2_DEFAULT_MEMORY_COST);
            }
            if (!isset($config[self::CONFIG_KEY_TIME_COST])) {
                $this->setTimeCost(PASSWORD_ARGON2_DEFAULT_TIME_COST);
            }
            if (!isset($config[self::CONFIG_KEY_THREADS])) {
                $this->setThreads(PASSWORD_ARGON2_DEFAULT_THREADS);
            }
        }
    }

    public function getMemoryCost(): int
    {
        return $this->memory_cost;
    }

    public function setMemoryCost(int $memory_costs): void
    {
        $this->memory_cost = $memory_costs;
    }

    public function getTimeCost(): int
    {
        return $this->time_cost;
    }

    public function setTimeCost(int $time_cost): void
    {
        $this->time_cost = $time_cost;
    }

    public function getThreads(): int
    {
        return $this->threads;
    }

    public function setThreads(int $threads): void
    {
        $this->threads = $threads;
    }

    public function getName(): string
    {
        return 'argon2id';
    }

    public function isSupportedByRuntime(): bool
    {
        return (
            parent::isSupportedByRuntime() &&
            version_compare(phpversion(), '7.3.0', '>=') &&
            defined('PASSWORD_ARGON2ID')
        );
    }

    public function encodePassword(string $raw, string $salt): string
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new ilPasswordException('Invalid password.');
        }

        return password_hash($raw, PASSWORD_ARGON2ID, [
            self::CONFIG_KEY_MEMORY_COST => $this->getMemoryCost(),
            self::CONFIG_KEY_TIME_COST => $this->getTimeCost(),
            self::CONFIG_KEY_THREADS => $this->getThreads(),
        ]);
    }

    public function isPasswordValid(string $encoded, string $raw, string $salt): bool
    {
        return password_verify($raw, $encoded);
    }

    public function requiresReencoding(string $encoded): bool
    {
        return password_needs_rehash($encoded, PASSWORD_ARGON2ID, [
            self::CONFIG_KEY_MEMORY_COST => $this->getMemoryCost(),
            self::CONFIG_KEY_TIME_COST => $this->getTimeCost(),
            self::CONFIG_KEY_THREADS => $this->getThreads(),
        ]);
    }
}
