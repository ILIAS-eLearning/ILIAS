<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBcryptPhpPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPhpPasswordEncoder extends ilBasePasswordEncoder
{
    /**
     * @var string
     */
    protected string $costs = '08';

    /**
     * @param array<string, mixed> $config
     * @throws ilPasswordException
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (strtolower($key) === 'cost') {
                $this->setCosts($value);
            }
        }

        if (!isset($config['cost']) && static::class === self::class) {
            // Determine the costs only if they are not passed in constructor
            $this->setCosts((string) $this->benchmarkCost());
        }

        $this->init();
    }

    protected function init() : void
    {
    }

    /**
     * @see http://php.net/manual/en/function.password-hash.php#example-984
     * @throws ilPasswordException
     */
    public function benchmarkCost(float $time_target = 0.05) : int
    {
        $cost = 8;

        do {
            ++$cost;
            $start = microtime(true);
            $encoder = new self(['cost' => (string) $cost]);
            $encoder->encodePassword('test', '');
            $end = microtime(true);
        } while (($end - $start) < $time_target && $cost < 32);

        return $cost;
    }

    public function getName() : string
    {
        return 'bcryptphp';
    }

    public function getCosts() : string
    {
        return $this->costs;
    }

    public function setCosts(string $costs) : void
    {
        if ($costs !== '') {
            $costs = (int) $costs;
            if ($costs < 4 || $costs > 31) {
                throw new ilPasswordException('The costs parameter of bcrypt must be in range 04-31');
            }
            $this->costs = sprintf('%1$02d', $costs);
        }
    }

    public function encodePassword(string $raw, string $salt) : string
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new ilPasswordException('Invalid password.');
        }

        return password_hash($raw, PASSWORD_BCRYPT, [
            'cost' => $this->getCosts()
        ]);
    }

    public function isPasswordValid(string $encoded, string $raw, string $salt) : bool
    {
        return password_verify($raw, $encoded);
    }

    public function requiresReencoding(string $encoded) : bool
    {
        return password_needs_rehash($encoded, PASSWORD_BCRYPT, [
            'cost' => $this->getCosts()
        ]);
    }
}
