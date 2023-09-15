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

namespace ILIAS\FileDelivery\Token\Signer\Key\Signing;

use ILIAS\FileDelivery\Token\Signer\Salt\Salt;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\Algorithm\Algorithm;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 * @internal
 */
class HMACSigningKeyGenerator implements SigningKeyGenerator
{
    public function __construct(
        private Algorithm $algorithm
    ) {
    }

    public function generate(SecretKey $secret_key, Salt $salt): SigningKey
    {
        return new SigningKey(hash_hmac($this->algorithm->getName(), $salt->get(), $secret_key->get(), false));
    }
}
