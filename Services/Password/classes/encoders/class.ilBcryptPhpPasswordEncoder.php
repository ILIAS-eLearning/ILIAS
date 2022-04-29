<?php declare(strict_types=1);

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

/**
 * Class ilBcryptPhpPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPhpPasswordEncoder extends ilBasePasswordEncoder
{
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
            $numeric_costs = (int) $costs;
            if ($numeric_costs < 4 || $numeric_costs > 31) {
                throw new ilPasswordException('The costs parameter of bcrypt must be in range 04-31');
            }
            $this->costs = sprintf('%1$02d', $numeric_costs);
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
