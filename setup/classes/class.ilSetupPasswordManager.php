<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSetupPasswordManager
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSetupPasswordManager
{
    /**
     * @var string
     */
    private $encoderName;

    /**
     * @var \ilSetupPasswordEncoderFactory
     */
    private $encoderFactory;

    /**
     * ilSetupPasswordManager constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                switch (strtolower($key)) {
                    case 'password_encoder':
                        $this->setEncoderName($value);
                        break;

                    case 'encoder_factory':
                        $this->setEncoderFactory($value);
                        break;
                }
            }
        }

        if (!$this->getEncoderName()) {
            throw new \Exception(sprintf('"password_encoder" must be set in %s.', json_encode($config)));
        }

        if (!($this->getEncoderFactory() instanceof \ilSetupPasswordEncoderFactory)) {
            throw new \ilUserException(sprintf('"encoder_factory" must be instance of \ilSetupPasswordEncoderFactory and set in %s.', json_encode($config)));
        }
    }

    /**
     * @return \ilSetupPasswordEncoderFactory
     */
    public function getEncoderFactory() : \ilSetupPasswordEncoderFactory
    {
        return $this->encoderFactory;
    }

    /**
     * @param \ilSetupPasswordEncoderFactory $encoderFactory
     */
    public function setEncoderFactory(\ilSetupPasswordEncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @return string
     */
    public function getEncoderName() : string
    {
        return $this->encoderName;
    }

    /**
     * @param string $encoderName
     */
    public function setEncoderName(string $encoderName)
    {
        $this->encoderName = $encoderName;
    }

    /**
     * Encodes the raw password based on the relevant encoding strategy
     * @param string $raw
     * @return string
     * @throws \ilUserException
     */
    public function encodePassword(string $raw) : string
    {
        $encoder = $this->getEncoderFactory()->getEncoderByName($this->getEncoderName());

        return $encoder->encodePassword($raw, '');
    }

    /**
     * Verifies if the passed raw password matches the encoded one, based on the current encoding strategy
     * @param string   $encoded
     * @param string   $raw
     * @param callable $passwordReHashCallback A callback passed by consumer, which accepts the raw password and is called
     *                                         if the encoder strategy decides a password has to be re-hashed.
     * @return bool
     * @throws \ilUserException
     */
    public function verifyPassword(string $encoded, string $raw, callable $passwordReHashCallback) : bool
    {
        $currentEncoder = $this->getEncoderFactory()->getFirstEncoderForEncodedPasswordAndMatchers(
            $encoded,
            [
                'md5'       => function ($encoded) {
                    return is_string($encoded) && strlen($encoded) === 32;
                },
                'bcryptphp' => function ($encoded) {
                    return is_string($encoded) && substr($encoded, 0, 4) === '$2y$' && strlen($encoded) === 60;
                }
            ]
        );

        if ($this->getEncoderName() != $currentEncoder->getName()) {
            if ($currentEncoder->isPasswordValid($encoded, $raw, '')) {
                $passwordReHashCallback($raw);
                return true;
            }
        } elseif ($currentEncoder->isPasswordValid($encoded, $raw, '')) {
            if ($currentEncoder->requiresReencoding($encoded)) {
                $passwordReHashCallback($raw);
            }

            return true;
        }

        return false;
    }
}
