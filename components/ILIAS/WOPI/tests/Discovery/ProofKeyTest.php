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

namespace ILIAS\WOPI\Discovery;

use PHPUnit\Framework\TestCase;

final class ProofKeyTest extends TestCase
{
    public function testPemContainsTheGivenModulusAndExponent(): void
    {
        $private = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $rsa = openssl_pkey_get_details($private)['rsa'];

        $key = new ProofKey(base64_encode($rsa['n']), base64_encode($rsa['e']));
        $loaded = openssl_pkey_get_details(openssl_pkey_get_public($key->toPem()));

        $this->assertSame($rsa['n'], $loaded['rsa']['n']);
        $this->assertSame($rsa['e'], $loaded['rsa']['e']);
    }

    public function testInvalidKeyIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ProofKey('not base64!', 'AQAB'))->toPem();
    }
}
