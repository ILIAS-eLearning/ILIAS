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

namespace ILIAS\FileDelivery\Token;

use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\FileDelivery\Token\Signer\Signer;
use ILIAS\FileDelivery\Token\Serializer\Serializer;
use ILIAS\FileDelivery\Token\Signer\Payload\Payload;
use ILIAS\FileDelivery\Token\Signer\HMACSigner;
use ILIAS\FileDelivery\Token\Serializer\JSONSerializer;
use ILIAS\FileDelivery\Token\Signer\Key\DigestMethod\Concat as NoneDigest;
use ILIAS\FileDelivery\Token\Signer\Key\Signing\HMACSigningKeyGenerator;
use ILIAS\FileDelivery\Token\Signer\Algorithm\SHA1;
use ILIAS\FileDelivery\Token\Compression\GZipCompression;
use ILIAS\FileDelivery\Token\Transport\URLSafeTransport;
use ILIAS\FileDelivery\Token\Compression\DeflateCompression;
use ILIAS\FileDelivery\Token\Signer\KeyRotatingSigner;
use ILIAS\FileDelivery\Token\Signer\Salt\Factory;
use ILIAS\FileDelivery\Token\Signer\Payload\StructuredPayload;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileDelivery\Token\Data\Stream;
use ILIAS\FileDelivery\Token\Compression\Compression;
use ILIAS\FileDelivery\Token\Transport\Transport;
use ILIAS\FileDelivery\Token\Signer\Payload\Builder;
use ILIAS\FileDelivery\Delivery\Disposition;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class DataSigner
{
    private Signer $signer;
    private Serializer $serializer;
    private SigningSerializer $signing_serializer;
    private Factory $salt_factory;
    private Compression $compression;
    private Transport $transport;
    private Builder $payload_builder;

    public function __construct(
        SecretKeyRotation $key_rotation
    ) {
        $this->salt_factory = new Factory();
        $compression = new DeflateCompression();
        $transport = new URLSafeTransport();
        $algorithm = new SHA1();

        $this->signing_serializer = new SigningSerializer(
            new KeyRotatingSigner(
                $key_rotation,
                new HMACSigner(
                    $algorithm
                ),
                new HMACSigningKeyGenerator(
                    $algorithm
                )
            ),
            new JSONSerializer(),
            $compression,
            $transport
        );

        $this->payload_builder = new Builder();
    }

    public function getSignedStreamToken(
        FileStream $stream,
        string $filename,
        Disposition $disposition,
        int $user_id,
        \DateTimeImmutable $until = null
    ): string {
        $payload = $this->payload_builder->shortFile(
            $stream,
            $filename,
            $disposition
        );

        if ($until !== null) {
            $payload->setUntil($until->getTimestamp());
        }

        return $this->signing_serializer->sign(
            $payload,
            $this->salt_factory->create('stream')
        );
    }

    public function verifyStreamToken(string $token): ?Payload
    {
        $data = $this->verify($token, 'stream');
        if ($data === null) {
            return null;
        }
        return $this->payload_builder->shortFileFromRaw($data);
    }

    public function sign(
        array $data,
        string $salt,
        \DateTimeImmutable $until = null
    ): string {
        $payload = new StructuredPayload($data);

        if ($until !== null) {
            $payload->setUntil($until->getTimestamp());
        }

        return $this->signing_serializer->sign(
            $payload,
            $this->salt_factory->create($salt)
        );
    }

    public function verify(
        string $token,
        string $salt
    ): ?array {
        return $this->signing_serializer->verify(
            $token,
            $this->salt_factory->create($salt)
        )?->get();
    }
}
