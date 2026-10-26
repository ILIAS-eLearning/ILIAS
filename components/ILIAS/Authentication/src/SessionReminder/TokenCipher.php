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

namespace ILIAS\Authentication\SessionReminder;

use ILIAS\Authentication\Setup\SessionReminderKeyCreatedObjective;

/**
 * Encodes the opaque token the session reminder sends to `sessioncheck.php`. The token is encrypted
 * (authenticated encryption, AES-256-GCM), so the client can neither read nor modify the contained
 * user id and session hash.
 *
 * Each token is encrypted with its own subkey, derived from the secret key and a random salt (HKDF).
 * This way the limited number of encryptions per key (because of the 96 bit nonce of GCM) does not apply
 * to the secret key, which is never replaced.
 *
 * Token format (base64url): salt (16 bytes) | nonce (12 bytes) | ciphertext | authentication tag (16 bytes)
 */
readonly class TokenCipher
{
    private const string CIPHER = 'aes-256-gcm';
    private const int KEY_LENGTH = 32;
    private const int SALT_LENGTH = 16;
    private const int NONCE_LENGTH = 12;
    private const int TAG_LENGTH = 16;
    /**
     * Binds the subkeys and the ciphertext to their purpose, a token of another context cannot be decrypted
     */
    private const string CONTEXT = 'ilias_session_reminder_v1';
    private const string USER_ID = 'u';
    private const string SESSION_HASH = 'h';

    /**
     * @param string $key Binary key
     */
    public function __construct(
        private string $key
    ) {
        if (\strlen($this->key) !== self::KEY_LENGTH) {
            throw new \InvalidArgumentException('Invalid key length.');
        }
    }

    /**
     * @return self|null NULL if the key has not been created, yet (see: `SessionReminderKeyCreatedObjective`),
     *                   or the cipher is not available
     */
    public static function fromKeyFile(): ?self
    {
        $path = SessionReminderKeyCreatedObjective::PATH();
        if (!\extension_loaded('openssl')
            || !is_readable($path)
            || !\in_array(self::CIPHER, openssl_get_cipher_methods(), true)) {
            return null;
        }

        $data = require $path;
        $key = \is_array($data) && \is_string($data[SessionReminderKeyCreatedObjective::KEY] ?? null)
            ? hex2bin($data[SessionReminderKeyCreatedObjective::KEY])
            : false;
        if ($key === false || \strlen($key) !== self::KEY_LENGTH) {
            return null;
        }

        return new self($key);
    }

    public function encode(int $user_id, string $session_hash): string
    {
        $salt = random_bytes(self::SALT_LENGTH);
        $nonce = random_bytes(self::NONCE_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt(
            json_encode([
                self::USER_ID => $user_id,
                self::SESSION_HASH => $session_hash,
            ], JSON_THROW_ON_ERROR),
            self::CIPHER,
            $this->deriveSubkey($salt),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            self::CONTEXT,
            self::TAG_LENGTH
        );
        if ($ciphertext === false) {
            throw new \RuntimeException('Could not encrypt the session reminder token.');
        }

        return rtrim(strtr(base64_encode($salt . $nonce . $ciphertext . $tag), '+/', '-_'), '=');
    }

    /**
     * @return array{user_id: int, session_hash: string}|null NULL if the token is invalid
     */
    public function decode(string $token): ?array
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $token) !== 1) {
            return null;
        }

        $raw = base64_decode(strtr($token, '-_', '+/'), true);
        if ($raw === false || \strlen($raw) <= self::SALT_LENGTH + self::NONCE_LENGTH + self::TAG_LENGTH) {
            return null;
        }

        $salt = substr($raw, 0, self::SALT_LENGTH);
        $nonce = substr($raw, self::SALT_LENGTH, self::NONCE_LENGTH);
        $ciphertext = substr($raw, self::SALT_LENGTH + self::NONCE_LENGTH, -self::TAG_LENGTH);
        $tag = substr($raw, -self::TAG_LENGTH);

        // Fails if the token has been modified (authentication tag)
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->deriveSubkey($salt),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            self::CONTEXT
        );
        if ($plaintext === false) {
            return null;
        }

        $data = json_decode($plaintext, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_int($data[self::USER_ID] ?? null) || !\is_string($data[self::SESSION_HASH] ?? null)) {
            return null;
        }

        return [
            'user_id' => $data[self::USER_ID],
            'session_hash' => $data[self::SESSION_HASH],
        ];
    }

    private function deriveSubkey(string $salt): string
    {
        return hash_hkdf('sha256', $this->key, self::KEY_LENGTH, self::CONTEXT, $salt);
    }
}
