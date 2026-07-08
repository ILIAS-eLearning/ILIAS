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

namespace ILIAS\WebDAV\Auth;

use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\Filesystem\Filesystem;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ILIASAuthenticationFileCache
{
    private const string SALT = 'webdav';
    private const string F_USERNAME = 'username';
    private const string F_USR_ID = 'usr_id';
    private const string F_PW_HASH = 'password';
    private DataSigner $data_signer;

    public function __construct(
        private Filesystem $filesystem,
        SecretKeyRotation $secret_key_rotation
    ) {
        $this->data_signer = new DataSigner(
            $secret_key_rotation
        );
    }

    private function getAuthCacheFile(string $username): string
    {
        return 'davcache_' . hash('sha256', $username);
    }

    private function readAuthCache(string $username): ?array
    {
        $file = $this->getAuthCacheFile($username);
        if (!$this->filesystem->has($file)) {
            return null;
        }
        $raw = $this->filesystem->read($file);

        return $this->data_signer->verify($raw, self::SALT);
    }

    private function writeAuthCache(string $username, string $password_hash, int $usr_id): void
    {
        $file = $this->getAuthCacheFile($username);

        $payload = [
            self::F_USR_ID => $usr_id,
            self::F_USERNAME => $username,
            self::F_PW_HASH => $password_hash,
        ];

        $payload = $this->data_signer->sign($payload, self::SALT);
        $this->filesystem->put($file, $payload);
    }

    public function isAuthenticated(string $username, string $password): ?int
    {
        $cached = $this->readAuthCache($username);
        if ($cached === null) {
            return null;
        }
        // has the password, since we stored it hashed
        $password = hash('sha256', $password);

        if ($cached[self::F_USERNAME] === $username && $cached[self::F_PW_HASH] === $password) {
            return (int) $cached[self::F_USR_ID]; // retun user_id if corrent
        }

        return null;
    }

    public function setAuthenticated(
        string $username,
        string $password,
        int $user_id
    ): void {
        // has the password, since we do not want to store them
        $password = hash('sha256', $password);

        $this->writeAuthCache($username, $password, $user_id);
    }

}
