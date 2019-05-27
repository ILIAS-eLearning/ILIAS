<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/exceptions/class.ilUserException.php';

/**
 * Class ilUserPasswordEncoderFactory
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUser
 */
class ilUserPasswordEncoderFactory
{
    /** @var string */
    protected $defaultEncoder;

    /**
     * @var ilPasswordEncoder[] Array of supported encoders
     */
    protected $encoders = array();

    /**
     * @param array $config
     * @throws ilPasswordException
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                switch (strtolower($key)) {
                    case 'default_password_encoder':
                        $this->setDefaultEncoder($value);
                        break;
                }
            }
        }

        $this->initEncoders($config);
    }

    /**
     * @param array $config
     * @return ilPasswordEncoder[]
     * @throws ilPasswordException
     */
    protected function getValidEncoders($config) : array
    {
        return [
            new ilBcryptPhpPasswordEncoder($config),
            new ilBcryptPasswordEncoder($config),
            new ilMd5PasswordEncoder($config),
        ];
    }

    /**
     * @param array $config
     * @throws ilPasswordException
     */
    protected function initEncoders(array $config)
    {
        $this->encoders = [];

        $encoders = $this->getValidEncoders($config);

        foreach ($encoders as $encoder) {
            if ($encoder->isSupportedByRuntime()) {
                $this->encoders[$encoder->getName()] = $encoder;
            }
        }
    }

    /**
     * @return string
     */
    public function getDefaultEncoder() : ?string
    {
        return $this->defaultEncoder;
    }

    /**
     * @param string $defaultEncoder
     */
    public function setDefaultEncoder(string $defaultEncoder)
    {
        $this->defaultEncoder = $defaultEncoder;
    }

    /**
     * @return ilPasswordEncoder[]
     */
    public function getEncoders() : array
    {
        return $this->encoders;
    }

    /**
     * @param ilPasswordEncoder[] $encoders
     * @throws ilUserException
     */
    public function setEncoders(array $encoders) : void
    {
        $this->encoders = array();
        foreach ($encoders as $encoder) {
            if (!($encoder instanceof ilPasswordEncoder)) {
                throw new ilUserException(sprintf(
                    'One of the passed encoders is not valid: %s.',
                    json_encode($encoder)
                ));
            }
            $this->encoders[$encoder->getName()] = $encoder;
        }
    }

    /**
     * @return string[]
     */
    public function getSupportedEncoderNames() : array
    {
        return array_keys($this->getEncoders());
    }

    /**
     * @param string $name
     * @param bool   $get_default_on_mismatch
     * @return ilPasswordEncoder
     * @throws ilUserException
     */
    public function getEncoderByName($name, $get_default_on_mismatch = false) : ilPasswordEncoder
    {
        if (!isset($this->encoders[$name])) {
            if (!$get_default_on_mismatch) {
                throw new ilUserException(sprintf('The encoder "%s" was not configured.', $name));
            } elseif (!$this->getDefaultEncoder()) {
                throw new ilUserException('No default encoder specified, fallback not possible.');
            } elseif (!isset($this->encoders[$this->getDefaultEncoder()])) {
                throw new ilUserException("No default encoder found for name: '{$this->getDefaultEncoder()}'.");
            }

            return $this->encoders[$this->getDefaultEncoder()];
        }

        return $this->encoders[$name];
    }

    /**
     * @param string $encoded
     * @param array  $matchers An key/value pair callback functions (accepting the encoded password) assigned to the respective encoder name
     * @return ilPasswordEncoder
     * @throws ilUserException
     */
    public function getFirstEncoderForEncodedPasswordAndMatchers(string $encoded, array $matchers) : ilPasswordEncoder
    {
        foreach ($this->getEncoders() as $encoder) {
            foreach ($matchers as $encoderName => $callback) {
                if (
                    $encoder->getName() === $encoderName &&
                    is_callable($callback) && $callback($encoded) === true
                ) {
                    return $encoder;
                }
            }
        }

        return $this->getEncoderByName($this->getDefaultEncoder());
    }
}
