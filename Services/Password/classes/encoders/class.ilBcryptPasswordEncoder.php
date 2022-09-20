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
 * Class ilBcryptPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 * @deprecated
 */
final class ilBcryptPasswordEncoder extends ilBcryptPhpPasswordEncoder
{
    /** @var int */
    private const MIN_SALT_SIZE = 16;

    /** @var string */
    public const SALT_STORAGE_FILENAME = 'pwsalt.txt';

    private ?string $client_salt = null;
    private bool $is_security_flaw_ignored = false;
    private bool $backward_compatibility = false;
    private string $data_directory = '';

    /**
     * @param array<string, mixed> $config
     * @throws ilPasswordException
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $key = strtolower($key);
            if ($key === 'ignore_security_flaw') {
                $this->setIsSecurityFlawIgnored($value);
            } elseif ($key === 'data_directory') {
                $this->setDataDirectory($value);
            }
        }

        parent::__construct($config);
        $this->readClientSalt();
    }

    private function isBcryptSupported(): bool
    {
        return PHP_VERSION_ID >= 50307;
    }

    public function getDataDirectory(): string
    {
        return $this->data_directory;
    }

    public function setDataDirectory(string $data_directory): void
    {
        $this->data_directory = $data_directory;
    }

    public function isBackwardCompatibilityEnabled(): bool
    {
        return $this->backward_compatibility;
    }

    /**
     * Set the backward compatibility $2a$ instead of $2y$ for PHP 5.3.7+
     */
    public function setBackwardCompatibility(bool $backward_compatibility): void
    {
        $this->backward_compatibility = $backward_compatibility;
    }

    public function isSecurityFlawIgnored(): bool
    {
        return $this->is_security_flaw_ignored;
    }

    public function setIsSecurityFlawIgnored(bool $is_security_flaw_ignored): void
    {
        $this->is_security_flaw_ignored = $is_security_flaw_ignored;
    }

    public function getClientSalt(): ?string
    {
        return $this->client_salt;
    }

    public function setClientSalt(?string $client_salt): void
    {
        $this->client_salt = $client_salt;
    }

    public function encodePassword(string $raw, string $salt): string
    {
        if (!$this->client_salt) {
            throw new ilPasswordException('Missing client salt.');
        }

        if ($this->isPasswordTooLong($raw)) {
            throw new ilPasswordException('Invalid password.');
        }

        return $this->encode($raw, $salt);
    }

    public function isPasswordValid(string $encoded, string $raw, string $salt): bool
    {
        if (!$this->client_salt) {
            throw new ilPasswordException('Missing client salt.');
        }

        return !$this->isPasswordTooLong($raw) && $this->check($encoded, $raw, $salt);
    }

    public function getName(): string
    {
        return 'bcrypt';
    }

    public function requiresSalt(): bool
    {
        return true;
    }

    public function requiresReencoding(string $encoded): bool
    {
        return false;
    }

    private function encode(string $raw, string $userSecret): string
    {
        $clientSecret = $this->client_salt;
        $hashedPassword = hash_hmac(
            'whirlpool',
            str_pad($raw, strlen($raw) * 4, sha1($userSecret), STR_PAD_BOTH),
            $clientSecret,
            true
        );
        $salt = substr(
            str_shuffle(str_repeat('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 22)),
            0,
            22
        );

        /**
         * Check for security flaw in the bcrypt implementation used by crypt()
         * @see http://php.net/security/crypt_blowfish.php
         */
        if ($this->isBcryptSupported() && !$this->backward_compatibility) {
            $prefix = '$2y$';
        } else {
            $prefix = '$2a$';
            // check if the password contains 8-bit character
            if (!$this->is_security_flaw_ignored && preg_match('#[\x80-\xFF]#', $raw)) {
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

    private function check(string $encoded, string $raw, string $salt): bool
    {
        $hashedPassword = hash_hmac(
            'whirlpool',
            str_pad($raw, strlen($raw) * 4, sha1($salt), STR_PAD_BOTH),
            $this->client_salt,
            true
        );

        return $this->comparePasswords($encoded, crypt($hashedPassword, substr($encoded, 0, 30)));
    }

    public function getClientSaltLocation(): string
    {
        return $this->data_directory . '/' . self::SALT_STORAGE_FILENAME;
    }

    private function readClientSalt(): void
    {
        if (is_file($this->getClientSaltLocation()) && is_readable($this->getClientSaltLocation())) {
            $contents = file_get_contents($this->getClientSaltLocation());
            if ($contents !== false && trim($contents) !== '') {
                $this->setClientSalt($contents);
            }
        } else {
            $this->generateClientSalt();
            $this->storeClientSalt();
        }
    }

    private function generateClientSalt(): void
    {
        $this->setClientSalt(
            substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(self::MIN_SALT_SIZE))), 0, 22)
        );
    }

    private function storeClientSalt(): void
    {
        $location = $this->getClientSaltLocation();

        set_error_handler(static function (int $severity, string $message, string $file, int $line): void {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        });

        try {
            $result = file_put_contents($location, $this->client_salt);
            if (!$result) {
                throw new ilPasswordException(sprintf(
                    'Could not store the client salt in: %s. Please contact an administrator.',
                    $location
                ));
            }
        } catch (Exception $e) {
            throw new ilPasswordException(sprintf(
                'Could not store the client salt in: %s. Please contact an administrator.',
                $location
            ), $e->getCode(), $e);
        } finally {
            restore_error_handler();
        }
    }
}
