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

use ILIAS\FileDelivery\Token\Serializer\Serializer;
use ILIAS\FileDelivery\Token\Signer\Payload\Payload;
use ILIAS\FileDelivery\Token\Signer\Payload\StructuredPayload;
use ILIAS\FileDelivery\Token\Compression\GZipCompression;
use ILIAS\FileDelivery\Token\Compression\Compression;
use ILIAS\FileDelivery\Token\Transport\Transport;
use ILIAS\FileDelivery\Token\Signer\KeyRotatingSigner;
use ILIAS\FileDelivery\Token\Signer\Salt\Salt;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 * @internal
 */
final class SigningSerializer
{
    private const SEPARATOR = '<<>>';

    public function __construct(
        private KeyRotatingSigner $signer,
        private Serializer $serializer,
        private Compression $compression,
        private Transport $transport
    ) {
    }

    public function sign(Payload $payload, Salt $salt): string
    {
        // serialize payload
        $serialized_payload = $this->serializer->serializePayload($payload->get());
        $serialized_validity = $this->serializer->serializeValidity($payload->until());
        $signable_payload = $serialized_payload . self::SEPARATOR . $serialized_validity;

        // sign payload
        $signature = $this->signer->sign($signable_payload, $salt);

        $signed_payload = $signable_payload . self::SEPARATOR . $signature;

        $compressed_payload = $this->compression->compress($signed_payload);

        $prepare_for_transport = $this->transport->prepareForTransport($compressed_payload);

        return $prepare_for_transport;
    }

    public function verify(string $data, Salt $salt): ?Payload
    {
        // decompress payload
        $decompressed_payload = $this->compression->decompress(
            $this->transport->readFromTransport($data)
        );

        $split_data = explode(self::SEPARATOR, $decompressed_payload);
        $serialized_payload = $split_data[0] ?? '';
        $validity = $split_data[1] ?? '';
        $signature = $split_data[2] ?? '';

        $payload_with_validity = $serialized_payload . self::SEPARATOR . $validity;

        if ($this->signer->verify($payload_with_validity, $signature, (int) $validity, $salt) === false) {
            return null;
        }

        return new StructuredPayload($this->serializer->unserializePayload($serialized_payload));
    }
}
