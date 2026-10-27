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

/**
 * A public RSA key of a WOPI client, as published in its discovery with base64 encoded
 * modulus and exponent.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final readonly class ProofKey
{
    public function __construct(
        public string $modulus,
        public string $exponent
    ) {
    }

    public function toPem(): string
    {
        $modulus = base64_decode($this->modulus, true);
        $exponent = base64_decode($this->exponent, true);
        if ($modulus === false || $exponent === false || $modulus === '' || $exponent === '') {
            throw new \InvalidArgumentException('Invalid proof key');
        }

        // SubjectPublicKeyInfo: SEQUENCE { SEQUENCE { rsaEncryption, NULL }, BIT STRING { RSAPublicKey } }
        $rsa_public_key = self::der(0x30, self::integer($modulus) . self::integer($exponent));
        $algorithm = self::der(0x30, self::der(0x06, "\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01") . self::der(0x05, ''));
        $info = self::der(0x30, $algorithm . self::der(0x03, "\x00" . $rsa_public_key));

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($info), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    private static function integer(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '' || (ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00" . $bytes;
        }

        return self::der(0x02, $bytes);
    }

    private static function der(int $tag, string $content): string
    {
        $length = strlen($content);
        if ($length < 0x80) {
            return chr($tag) . chr($length) . $content;
        }
        $length_bytes = ltrim(pack('N', $length), "\x00");

        return chr($tag) . chr(0x80 | strlen($length_bytes)) . $length_bytes . $content;
    }
}
