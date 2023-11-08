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

namespace ILIAS\FileDelivery\Token\Signer\Salt;

/**
 * The salt is combined with the secret key to derive a unique key for distinguishing different contexts. Unlike the
 * secret key, the salt doesn’t have to be random, and can be saved in code. It only has to be unique between
 * contexts, not private.
 *
 * For example, you want to email activation links to activate user accounts, and upgrade links to upgrade users to a
 * paid accounts. If all you sign is the user id, and you don’t use different salts, a user could reuse the token from
 * the activation link to upgrade the account. If you use different salts, the signatures will be different and will
 * not be valid in the other context.
 *
 * @see    https://itsdangerous.palletsprojects.com/en/2.1.x/concepts/#serializer-vs-signer
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Factory
{
    private array $known_salts;

    public function __construct()
    {
        $this->known_salts = []; // TODO: load known salts from artifact
    }

    public function create(string $salt): Salt
    {
        if (!in_array($salt, $this->known_salts, true)) {
            // throw new \InvalidArgumentException('Unknown salt'); // currently disabled because of missing artifact
        }

        return new Salt($salt);
    }
}
