<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/encoders/class.ilBcryptPhpPasswordEncoder.php';

/**
 * Class ilBcryptPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPasswordEncoder extends ilBcryptPhpPasswordEncoder
{
    /**
     * @var int
     */
    const MIN_SALT_SIZE = 16;

    /**
     * @var string
     */
    const SALT_STORAGE_FILENAME = 'pwsalt.txt';

    /**
     * @var string|null
     */
    private $client_salt = null;

    /**
     * @var bool
     */
    private $is_security_flaw_ignored = false;

    /**
     * @var bool
     */
    private $backward_compatibility = false;

    /**
     * @var string
     */
    private $data_directory = '';

    /**
     * @param array $config
     * @throws ilPasswordException
     */
    public function __construct(array $config = array())
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
     *
     */
    protected function init()
    {
        $this->readClientSalt();
    }

    /**
     * @return bool
     */
    protected function isBcryptSupported()
    {
        return PHP_VERSION_ID >= 50307;
    }

    /**
     * @return string
     */
    public function getDataDirectory()
    {
        return $this->data_directory;
    }

    /**
     * @param string $data_directory
     */
    public function setDataDirectory($data_directory)
    {
        $this->data_directory = $data_directory;
    }

    /**
     * @return boolean
     */
    public function isBackwardCompatibilityEnabled()
    {
        return (bool) $this->backward_compatibility;
    }

    /**
     * Set the backward compatibility $2a$ instead of $2y$ for PHP 5.3.7+
     * @param boolean $backward_compatibility
     */
    public function setBackwardCompatibility($backward_compatibility)
    {
        $this->backward_compatibility = (bool) $backward_compatibility;
    }

    /**
     * @return boolean
     */
    public function isSecurityFlawIgnored()
    {
        return (bool) $this->is_security_flaw_ignored;
    }

    /**
     * @param boolean $is_security_flaw_ignored
     */
    public function setIsSecurityFlawIgnored($is_security_flaw_ignored)
    {
        $this->is_security_flaw_ignored = (bool) $is_security_flaw_ignored;
    }

    /**
     * @return string|null
     */
    public function getClientSalt()
    {
        return $this->client_salt;
    }

    /**
     * @param string|null $client_salt
     */
    public function setClientSalt($client_salt)
    {
        $this->client_salt = $client_salt;
    }

    /**
     * {@inheritdoc}
     * @throws ilPasswordException
     */
    public function encodePassword($raw, $salt)
    {
        if (!$this->getClientSalt()) {
            require_once 'Services/Password/exceptions/class.ilPasswordException.php';
            throw new ilPasswordException('Missing client salt.');
        }

        if ($this->isPasswordTooLong($raw)) {
            require_once 'Services/Password/exceptions/class.ilPasswordException.php';
            throw new ilPasswordException('Invalid password.');
        }

        return $this->encode($raw, $salt);
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        if (!$this->getClientSalt()) {
            require_once 'Services/Password/exceptions/class.ilPasswordException.php';
            throw new ilPasswordException('Missing client salt.');
        }

        return !$this->isPasswordTooLong($raw) && $this->check($encoded, $raw, $salt);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bcrypt';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSalt()
    {
        return true;
    }



    /**
     * {@inheritdoc}
     */
    public function requiresReencoding($encoded)
    {
        return false;
    }

    /**
     * Generates a bcrypt encoded string
     * @param    string $raw         The raw password
     * @param    string $user_secret A randomly generated string (should be 16 ASCII chars)
     * @return   string
     * @throws   ilPasswordException
     */
    protected function encode($raw, $user_secret)
    {
        $client_secret = $this->getClientSalt();
        $hashed_password = hash_hmac('whirlpool', str_pad($raw, strlen($raw) * 4, sha1($user_secret), STR_PAD_BOTH), $client_secret, true);
        $salt = substr(str_shuffle(str_repeat('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 22)), 0, 22);

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
                require_once 'Services/Password/exceptions/class.ilPasswordException.php';
                throw new ilPasswordException(
                    'The bcrypt implementation used by PHP can contain a security flaw ' .
                    'using passwords with 8-bit characters. ' .
                    'We suggest to upgrade to PHP 5.3.7+ or use passwords with only 7-bit characters.'
                );
            }
        }

        $salted_password = crypt($hashed_password, $prefix . $this->getCosts() . '$' . $salt);
        if (strlen($salted_password) <= 13) {
            require_once 'Services/Password/exceptions/class.ilPasswordException.php';
            throw new ilPasswordException('Error during the bcrypt generation');
        }

        return $salted_password;
    }

    /**
     * Verifies a bcrypt encoded string
     * @param    string $encoded
     * @param    string $raw
     * @param    string $salt
     * @return   bool
     */
    protected function check($encoded, $raw, $salt)
    {
        $hashed_password = hash_hmac('whirlpool', str_pad($raw, strlen($raw) * 4, sha1($salt), STR_PAD_BOTH), $this->getClientSalt(), true);
        return crypt($hashed_password, substr($encoded, 0, 30)) == $encoded;
    }

    /**
     * @return string
     */
    public function getClientSaltLocation()
    {
        return $this->getDataDirectory() . '/' . self::SALT_STORAGE_FILENAME;
    }

    /**
     *
     */
    private function readClientSalt()
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
    private function generateClientSalt()
    {
        require_once 'Services/Password/classes/class.ilPasswordUtils.php';
        $this->setClientSalt(
            substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(self::MIN_SALT_SIZE))), 0, 22)
        );
    }

    /**
     * @throws ilPasswordException
     */
    private function storeClientSalt()
    {
        $result = @file_put_contents($this->getClientSaltLocation(), $this->getClientSalt());
        if (!$result) {
            require_once 'Services/Password/exceptions/class.ilPasswordException.php';
            throw new ilPasswordException(sprintf("Could not store the client salt in: %s. Please contact an administrator.", $this->getClientSaltLocation()));
        }
    }
}
