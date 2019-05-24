<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/encoders/class.ilBcryptPhpPasswordEncoder.php';

/**
 * Class ilBcryptPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPasswordEncoder extends ilBcryptPhpPasswordEncoder
{
    /** @var int */
    const MIN_SALT_SIZE = 16;

    /** @var string */
    const SALT_STORAGE_FILENAME = 'pwsalt.txt';

    /** @var string|null */
    private $client_salt = null;

    /** @var bool */
    private $is_security_flaw_ignored = false;

    /** @var bool */
    private $backward_compatibility = false;

    /** @var string */
    private $data_directory = '';

    /**
     * @param array $config
     * @throws ilPasswordException
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                switch (strtolower($key)) {
                    case 'ignore_security_flaw':
                        $this->setIsSecurityFlawIgnored($value);
                        break;

                    case 'data_directory':
                        $this->setDataDirectory($value);
                        break;
                }
            }
        }

        parent::__construct($config);
    }

    /**
     * @throws ilPasswordException
     */
    protected function init() : void
    {
        $this->readClientSalt();
    }

    /**
     * @return bool
     */
    protected function isBcryptSupported() : bool
    {
        return PHP_VERSION_ID >= 50307;
    }

    /**
     * @return string
     */
    public function getDataDirectory() : string
    {
        return $this->data_directory;
    }

    /**
     * @param string $data_directory
     */
    public function setDataDirectory(string $data_directory) : void
    {
        $this->data_directory = $data_directory;
    }

    /**
     * @return boolean
     */
    public function isBackwardCompatibilityEnabled() : bool
    {
        return (bool) $this->backward_compatibility;
    }

    /**
     * Set the backward compatibility $2a$ instead of $2y$ for PHP 5.3.7+
     * @param boolean $backward_compatibility
     */
    public function setBackwardCompatibility(bool $backward_compatibility) : void
    {
        $this->backward_compatibility = (bool) $backward_compatibility;
    }

    /**
     * @return boolean
     */
    public function isSecurityFlawIgnored() : bool
    {
        return (bool) $this->is_security_flaw_ignored;
    }

    /**
     * @param boolean $is_security_flaw_ignored
     */
    public function setIsSecurityFlawIgnored(bool $is_security_flaw_ignored) : void
    {
        $this->is_security_flaw_ignored = (bool) $is_security_flaw_ignored;
    }

    /**
     * @return string|null
     */
    public function getClientSalt() : ?string
    {
        return $this->client_salt;
    }

    /**
     * @param string|null $client_salt
     */
    public function setClientSalt(?string $client_salt)
    {
        $this->client_salt = $client_salt;
    }

    /**
     * @inheritDoc
     * @throws ilPasswordException
     */
    public function encodePassword(string $raw, string $salt) : string
    {
        if (!$this->getClientSalt()) {
            throw new ilPasswordException('Missing client salt.');
        }

        if ($this->isPasswordTooLong($raw)) {
            throw new ilPasswordException('Invalid password.');
        }

        return $this->encode($raw, $salt);
    }

    /**
     * @inheritDoc
     * @throws ilPasswordException
     */
    public function isPasswordValid(string $encoded, string $raw, string $salt) : bool
    {
        if (!$this->getClientSalt()) {
            throw new ilPasswordException('Missing client salt.');
        }

        return !$this->isPasswordTooLong($raw) && $this->check($encoded, $raw, $salt);
    }

    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return 'bcrypt';
    }

    /**
     * @inheritDoc
     */
    public function requiresSalt() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function requiresReencoding(string $encoded) : bool
    {
        return false;
    }

    /**
     * Generates a bcrypt encoded string
     * @param string $raw        The raw password
     * @param string $userSecret A randomly generated string (should be 16 ASCII chars)
     * @return   string
     * @throws   ilPasswordException
     */
    protected function encode(string $raw, string $userSecret) : string
    {
        $clientSecret   = $this->getClientSalt();
        $hashedPassword = hash_hmac(
            'whirlpool',
            str_pad($raw, strlen($raw) * 4, sha1($userSecret), STR_PAD_BOTH),
            $clientSecret,
            true
        );
        $salt           = substr(
            str_shuffle(str_repeat('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 22)),
            0,
            22
        );

        /**
         * Check for security flaw in the bcrypt implementation used by crypt()
         * @see http://php.net/security/crypt_blowfish.php
         */
        if ($this->isBcryptSupported() && !$this->isBackwardCompatibilityEnabled()) {
            $prefix = '$2y$';
        } else {
            $prefix = '$2a$';
            // check if the password contains 8-bit character
            if (!$this->isSecurityFlawIgnored() && preg_match('/[\x80-\xFF]/', $raw)) {
                throw new ilPasswordException(
                    'The bcrypt implementation used by PHP can contain a security flaw ' .
                    'using passwords with 8-bit characters. ' .
                    'We suggest to upgrade to PHP 5.3.7+ or use passwords with only 7-bit characters.'
                );
            }
        }

        $saltedPassword = crypt($hashedPassword, $prefix . $this->getCosts() . '$' . $salt);
        if (strlen($saltedPassword) <= 13) {
            throw new ilPasswordException('Error during the bcrypt generation');
        }

        return $saltedPassword;
    }

    /**
     * Verifies a bcrypt encoded string
     * @param string $encoded
     * @param string $raw
     * @param string $salt
     * @return   bool
     */
    protected function check(string $encoded, string $raw, string $salt) : bool
    {
        $hashedPassword = hash_hmac(
            'whirlpool',
            str_pad($raw, strlen($raw) * 4, sha1($salt), STR_PAD_BOTH),
            $this->getClientSalt(),
            true
        );

        return $this->comparePasswords($encoded, crypt($hashedPassword, substr($encoded, 0, 30)));
    }

    /**
     * @return string
     */
    public function getClientSaltLocation() : string
    {
        return $this->getDataDirectory() . '/' . self::SALT_STORAGE_FILENAME;
    }

    /**
     * @throws ilPasswordException
     */
    private function readClientSalt() : void
    {
        if (is_file($this->getClientSaltLocation()) && is_readable($this->getClientSaltLocation())) {
            $contents = file_get_contents($this->getClientSaltLocation());
            if (strlen(trim($contents))) {
                $this->setClientSalt($contents);
            }
        } else {
            $this->generateClientSalt();
            $this->storeClientSalt();
        }
    }

    /**
     *
     */
    private function generateClientSalt() : void
    {
        $this->setClientSalt(
            substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(self::MIN_SALT_SIZE))), 0, 22)
        );
    }

    /**
     * @throws ilPasswordException
     */
    private function storeClientSalt() : void
    {
        $result = @file_put_contents($this->getClientSaltLocation(), $this->getClientSalt());
        if (!$result) {
            throw new ilPasswordException(sprintf(
                "Could not store the client salt in: %s. Please contact an administrator.",
                $this->getClientSaltLocation()
            ));
        }
    }
}
