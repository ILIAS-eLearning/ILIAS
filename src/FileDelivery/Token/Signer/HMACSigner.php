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

namespace ILIAS\FileDelivery\Token\Signer;

use ILIAS\FileDelivery\Token\Signer\Key\DigestMethod\DigestMethod;
use ILIAS\FileDelivery\Token\Signer\Algorithm\Algorithm;
use ILIAS\FileDelivery\Token\Signer\Key\Signing\SigningKey;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class HMACSigner implements Signer
{
    public function __construct(
        private Algorithm $algorithm
    ) {
    }

    protected function getAlgorithm(): string
    {
        return $this->algorithm->getName();
    }

    public function sign(
        string $signable_payload,
        SigningKey $signing_key
    ): string {
        // sign the payload using hmac_hash
        return hash_hmac(
            $this->getAlgorithm(),
            $signable_payload,
            $signing_key->get(),
            false
        );
    }

    public function verify(
        string $data,
        string $signature,
        int $validity,
        SigningKey $signing_key
    ): bool {
        $signature_check = $this->sign($data, $signing_key);
        if ($signature_check !== $signature) {
            return false;
        }
        if ($validity > 0 && $validity < time()) {
            return false;
        }

        return true;
    }
}
