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

namespace ILIAS\FileDelivery\Token\Signer\Key\Secret;

/**
 * Signatures are secured by the secret_key. Typically one secret key is used with all signers, and the salt is used to
 * distinguish different contexts. Changing the secret key will invalidate existing tokens.
 *
 * It should be a long random string of bytes. This value must be kept secret and should not be saved in source code or
 * committed to version control. If an attacker learns the secret key, they can change and resign data to look valid.
 * If you suspect this happened, change the secret key to invalidate existing tokens.
 *
 * One way to keep the secret key separate is to read it from an environment variable. When deploying for the first time,
 * generate a key and set the environment variable when running the application.
 * All process managers (like systemd) and hosting services have a way to specify environment variables.
 *
 * @see    https://itsdangerous.palletsprojects.com/en/2.1.x/concepts/#the-secret-key
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class SecretKey
{
    public function __construct(
        private string $secret_key
    ) {
    }

    public function get(): string
    {
        return $this->secret_key;
    }
}
