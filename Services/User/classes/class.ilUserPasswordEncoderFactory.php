<?php

declare(strict_types=1);

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
 * Class ilUserPasswordEncoderFactory
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUser
 */
class ilUserPasswordEncoderFactory
{
    protected ?string $defaultEncoder = null;
    /** @var array<string, ilPasswordEncoder> Array of supported encoders */
    protected array $encoders = [];

    /**
     * @param array $config
     * @throws ilPasswordException
     */
    public function __construct(array $config = []) // Missing array type.
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
    protected function getValidEncoders(array $config): array // Missing array type.
    {
        return [
            new ilBcryptPhpPasswordEncoder($config),
            new ilBcryptPasswordEncoder($config),
            new ilMd5PasswordEncoder(),
        ];
    }

    /**
     * @param array $config
     * @throws ilPasswordException
     */
    protected function initEncoders(array $config): void // Missing array type.
    {
        $this->encoders = [];

        $encoders = $this->getValidEncoders($config);
        foreach ($encoders as $encoder) {
            if ($encoder->isSupportedByRuntime()) {
                $this->encoders[$encoder->getName()] = $encoder;
            }
        }
    }

    public function getDefaultEncoder(): ?string
    {
        return $this->defaultEncoder;
    }

    public function setDefaultEncoder(string $defaultEncoder): void
    {
        $this->defaultEncoder = $defaultEncoder;
    }

    /**
     * @return array<string, ilPasswordEncoder>
     */
    public function getEncoders(): array
    {
        return $this->encoders;
    }

    /**
     * @param ilPasswordEncoder[] $encoders
     * @throws ilUserException
     */
    public function setEncoders(array $encoders): void
    {
        $this->encoders = [];
        foreach ($encoders as $encoder) {
            if (!($encoder instanceof ilPasswordEncoder)) {
                throw new ilUserException(sprintf(
                    'One of the passed encoders is not valid: %s.',
                    json_encode($encoder, JSON_THROW_ON_ERROR)
                ));
            }
            $this->encoders[$encoder->getName()] = $encoder;
        }
    }

    /**
     * @return string[]
     */
    public function getSupportedEncoderNames(): array
    {
        return array_keys($this->getEncoders());
    }

    /**
     * @param string $name
     * @param bool $get_default_on_mismatch
     * @return ilPasswordEncoder
     * @throws ilUserException
     */
    public function getEncoderByName(string $name, bool $get_default_on_mismatch = false): ilPasswordEncoder
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
     * @param array $matchers An key/value pair callback functions (accepting the encoded password) assigned to the respective encoder name
     * @return ilPasswordEncoder
     * @throws ilUserException
     */
    public function getFirstEncoderForEncodedPasswordAndMatchers(string $encoded, array $matchers): ilPasswordEncoder
    {
        foreach ($this->getEncoders() as $encoder) {
            foreach ($matchers as $encoderName => $callback) {
                if (
                    is_callable($callback) &&
                    $encoder->getName() === $encoderName &&
                    $callback($encoded) === true
                ) {
                    return $encoder;
                }
            }
        }

        return $this->getEncoderByName($this->getDefaultEncoder());
    }
}
