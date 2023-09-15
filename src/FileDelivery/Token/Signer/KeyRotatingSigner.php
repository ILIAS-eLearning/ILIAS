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

use ILIAS\FileDelivery\Token\Signer\Key\Signing\SigningKeyGenerator;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\FileDelivery\Token\Signer\Salt\Salt;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class KeyRotatingSigner
{
    private SecretKey $current_secret_key;

    public function __construct(
        private SecretKeyRotation $key_rotation,
        private Signer $signer,
        private SigningKeyGenerator $signing_key_generator,
    ) {
        $this->current_secret_key = $key_rotation->getCurrentKey();
    }

    public function sign(string $signable_payload, Salt $salt): string
    {
        return $this->signer->sign(
            $signable_payload,
            $this->signing_key_generator->generate(
                $this->current_secret_key,
                $salt
            )
        );
    }

    public function verify(
        string $data,
        string $signature,
        int $validity,
        Salt $salt
    ): bool {
        foreach ($this->key_rotation->getAllKeys() as $secret_key) {
            $signing_key = $this->signing_key_generator->generate($secret_key, $salt);
            if ($this->signer->verify(
                $data,
                $signature,
                $validity,
                $signing_key
            )) {
                return true;
            }
        }
        return false;
    }
}
