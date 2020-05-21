<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserPasswordEncoderFactory
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUser
 */
class ilUserPasswordEncoderFactory
{
    /** @var string */
    protected $defaultEncoder;

    /** @var ilPasswordEncoder[] */
    protected $supportedEncoders = [];

    /**
     * @param array<string, mixed> $config
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
     * @param array<string, mixed> $config
     * @return ilPasswordEncoder[]
     * @throws ilPasswordException
     */
    protected function getValidEncoders(array $config) : array
    {
        return [
            new ilArgon2idPasswordEncoder($config),
            new ilBcryptPhpPasswordEncoder($config),
            new ilBcryptPasswordEncoder($config),
            new ilMd5PasswordEncoder($config),
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @throws ilPasswordException
     */
    protected function initEncoders(array $config)
    {
        $this->supportedEncoders = [];

        $encoders = $this->getValidEncoders($config);

        foreach ($encoders as $encoder) {
            if ($encoder->isSupportedByRuntime()) {
                $this->supportedEncoders[$encoder->getName()] = $encoder;
            }
        }
    }

    /**
     * @return string
     */
    public function getDefaultEncoder() : string
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
    public function getSupportedEncoders() : array
    {
        return $this->supportedEncoders;
    }

    /**
     * @param ilPasswordEncoder[] $supportedEncoders
     * @throws ilUserException
     */
    public function setSupportedEncoders(array $supportedEncoders) : void
    {
        $this->supportedEncoders = [];

        foreach ($supportedEncoders as $encoder) {
            if (!($encoder instanceof ilPasswordEncoder)) {
                throw new ilUserException(sprintf(
                    'One of the passed encoders is not valid: %s.',
                    print_r($encoder, true)
                ));
            }
            $this->supportedEncoders[$encoder->getName()] = $encoder;
        }
    }

    /**
     * @return string[]
     */
    public function getSupportedEncoderNames() : array
    {
        return array_keys($this->getSupportedEncoders());
    }

    /**
     * @param string $name
     * @param bool   $fallbackToDefault
     * @return ilPasswordEncoder
     * @throws ilUserException
     */
    public function getEncoderByName($name, $fallbackToDefault = false) : ilPasswordEncoder
    {
        if (!isset($this->supportedEncoders[$name])) {
            if (!$fallbackToDefault) {
                throw new ilUserException(sprintf('The encoder "%s" was not configured.', $name));
            } elseif (!$this->getDefaultEncoder()) {
                throw new ilUserException('No default encoder specified, fallback not possible.');
            } elseif (!isset($this->supportedEncoders[$this->getDefaultEncoder()])) {
                throw new ilUserException("No default encoder found for name: '{$this->getDefaultEncoder()}'.");
            }

            return $this->supportedEncoders[$this->getDefaultEncoder()];
        }

        return $this->supportedEncoders[$name];
    }

    /**
     * @param string $encoded
     * @param array  $matchers An key/value pair callback functions (accepting the encoded password) assigned to the respective encoder name
     * @return ilPasswordEncoder
     * @throws ilUserException
     */
    public function getFirstEncoderForEncodedPasswordAndMatchers(string $encoded, array $matchers) : ilPasswordEncoder
    {
        foreach ($this->getSupportedEncoders() as $encoder) {
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
