<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/class.ilBasePasswordEncoder.php';

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
    protected $costs = '08';

    /**
     * @param array $config
     * @throws ilPasswordException
     */
    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                switch (strtolower($key)) {
                    case 'cost':
                        $this->setCosts($value);
                        break;
                }
            }
        }

        if (!isset($config['cost']) && static::class == self::class) {
            // Determine the costs only if they are not passed in constructor
            $this->setCosts($this->benchmarkCost(0.05));
        }

        $this->init();
    }

    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @see http://php.net/manual/en/function.password-hash.php#example-984
     * @param float $time_target
     * @return int
     */
    public function benchmarkCost($time_target = 0.05)
    {
        $cost = 8;

        do {
            $cost++;
            $start = microtime(true);
            $encoder = new self(array('cost' => $cost));
            $encoder->encodePassword('test', '');
            $end = microtime(true);
        } while (($end - $start) < $time_target && $cost < 32);

        return $cost;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bcryptphp';
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedByRuntime()
    {
        return parent::isSupportedByRuntime() && version_compare(phpversion(), '5.5.0', '>=');
    }

    /**
     * @return string
     */
    public function getCosts()
    {
        return $this->costs;
    }

    /**
     * @param string $costs
     * @throws ilPasswordException
     */
    public function setCosts($costs)
    {
        if (!empty($costs)) {
            $costs = (int) $costs;
            if ($costs < 4 || $costs > 31) {
                require_once 'Services/Password/exceptions/class.ilPasswordException.php';
                throw new ilPasswordException('The costs parameter of bcrypt must be in range 04-31');
            }
            $this->costs = sprintf('%1$02d', $costs);
        }
    }

    /**
     * {@inheritdoc}
     * @throws ilPasswordException
     */
    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            require_once 'Services/Password/exceptions/class.ilPasswordException.php';
            throw new ilPasswordException('Invalid password.');
        }

        return password_hash($raw, PASSWORD_BCRYPT, array(
            'cost' => $this->getCosts()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return password_verify($raw, $encoded);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresReencoding($encoded)
    {
        return password_needs_rehash($encoded, PASSWORD_BCRYPT, array(
            'cost' => $this->getCosts()
        ));
    }
}
