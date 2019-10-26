<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilArgon2idPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilArgon2idPasswordEncoder extends ilBasePasswordEncoder
{
    /** @var int */
    private $memory_cost;
    /** @var int */
    private $time_cost;
    /** @var int */
    private $threads;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                switch (strtolower($key)) {
                    case 'memory_cost':
                        $this->setMemoryCost($value);
                        break;

                    case 'time_cost':
                        $this->setTimeCost($value);
                        break;

                    case 'threads':
                        $this->setThreads($value);
                        break;
                }
            }
        }

        if ($this->isSupportedByRuntime() && static::class == self::class) {
            if (!isset($config['memory_cost'])) {
                $this->setMemoryCost(PASSWORD_ARGON2_DEFAULT_MEMORY_COST);
            }
            if (!isset($config['time_cost'])) {
                $this->setTimeCost(PASSWORD_ARGON2_DEFAULT_TIME_COST);
            }
            if (!isset($config['threads'])) {
                $this->setThreads(PASSWORD_ARGON2_DEFAULT_THREADS);
            }
        }

        $this->init();
    }

    /**
     *
     */
    protected function init() : void
    {
    }

    /**
     * @return int
     */
    public function getMemoryCost() : int
    {
        return $this->memory_cost;
    }

    /**
     * @param int $memory_costs
     */
    public function setMemoryCost(int $memory_costs) : void
    {
        $this->memory_cost = $memory_costs;
    }

    /**
     * @return int
     */
    public function getTimeCost() : int
    {
        return $this->time_cost;
    }

    /**
     * @param int $time_cost
     */
    public function setTimeCost(int $time_cost) : void
    {
        $this->time_cost = $time_cost;
    }

    /**
     * @return int
     */
    public function getThreads() : int
    {
        return $this->threads;
    }

    /**
     * @param int $threads
     */
    public function setThreads(int $threads) : void
    {
        $this->threads = $threads;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return 'argon2id';
    }

    /**
     * @inheritDoc
     */
    public function isSupportedByRuntime() : bool
    {
        return (
            parent::isSupportedByRuntime() &&
            version_compare(phpversion(), '7.3.0', '>=') &&
            defined('PASSWORD_ARGON2ID')
        );
    }

    /**
     * @inheritDoc
     * @throws ilPasswordException
     */
    public function encodePassword(string $raw, string $salt) : string
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new ilPasswordException('Invalid password.');
        }

        return password_hash($raw, PASSWORD_ARGON2ID, [
            'memory_cost' => $this->getMemoryCost(),
            'time_cost' => $this->getTimeCost(),
            'threads' => $this->getThreads(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isPasswordValid(string $encoded, string $raw, string $salt) : bool
    {
        return password_verify($raw, $encoded);
    }

    /**
     * @inheritDoc
     */
    public function requiresReencoding(string $encoded) : bool
    {
        return password_needs_rehash($encoded, PASSWORD_ARGON2ID, [
            'memory_cost' => $this->getMemoryCost(),
            'time_cost' => $this->getTimeCost(),
            'threads' => $this->getThreads(),
        ]);
    }
}