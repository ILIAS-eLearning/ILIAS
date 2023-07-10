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

class ilUserPasswordEncoderFactory
{
    private ?string $default_encoder = null;
    /** @var array<string, ilPasswordEncoder> Array of supported encoders */
    private array $supported_encoders = [];

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
     * @return list<ilPasswordEncoder>
     * @throws ilPasswordException
     */
    private function getEncoders(array $config): array
    {
        return [
            new ilArgon2idPasswordEncoder($config),
            new ilBcryptPhpPasswordEncoder($config),
            new ilBcryptPasswordEncoder($config),
            new ilMd5PasswordEncoder(),
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @throws ilPasswordException
     */
    private function initEncoders(array $config): void
    {
        $this->supported_encoders = [];

        $encoders = $this->getEncoders($config);
        foreach ($encoders as $encoder) {
            if ($encoder->isSupportedByRuntime()) {
                $this->supported_encoders[$encoder->getName()] = $encoder;
            }
        }
    }

    public function getDefaultEncoder(): ?string
    {
        return $this->default_encoder;
    }

    public function setDefaultEncoder(string $default_encoder): void
    {
        $this->default_encoder = $default_encoder;
    }

    /**
     * @return array<string, ilPasswordEncoder>
     */
    public function getSupportedEncoders(): array
    {
        return $this->supported_encoders;
    }

    /**
     * @param list<ilPasswordEncoder> $supported_encoders
     * @throws ilUserException
     */
    public function setSupportedEncoders(array $supported_encoders): void
    {
        $this->supported_encoders = [];
        foreach ($supported_encoders as $encoder) {
            if (!($encoder instanceof ilPasswordEncoder) || !$encoder->isSupportedByRuntime()) {
                throw new ilUserException(sprintf(
                    'One of the passed encoders is not valid: %s.',
                    print_r($encoder, true)
                ));
            }
            $this->supported_encoders[$encoder->getName()] = $encoder;
        }
    }

    /**
     * @return list<string>
     */
    public function getSupportedEncoderNames(): array
    {
        return array_keys($this->getSupportedEncoders());
    }

    /**
     * @throws ilUserException
     */
    public function getEncoderByName(string $name): ilPasswordEncoder
    {
        if (!isset($this->supported_encoders[$name])) {
            if (!$this->getDefaultEncoder()) {
                throw new ilUserException('No default encoder specified, fallback not possible.');
            } elseif (!isset($this->supported_encoders[$this->getDefaultEncoder()])) {
                throw new ilUserException("No default encoder found for name: '{$this->getDefaultEncoder()}'.");
            }

            return $this->supported_encoders[$this->getDefaultEncoder()];
        }

        return $this->supported_encoders[$name];
    }
}
