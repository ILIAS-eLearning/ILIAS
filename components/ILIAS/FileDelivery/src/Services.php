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

namespace ILIAS\FileDelivery;

use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Services
{
    public const DELIVERY_ENDPOINT = '/components/ILIAS/FileDelivery/src/deliver.php/';

    public function __construct(
        private \ILIAS\FileDelivery\Delivery\StreamDelivery $delivery,
        private LegacyDelivery $legacy_delivery,
        private DataSigner $data_signer
    ) {
    }

    public function delivery(): \ILIAS\FileDelivery\Delivery\StreamDelivery
    {
        return $this->delivery;
    }

    public function legacyDelivery(): LegacyDelivery
    {
        return $this->legacy_delivery;
    }

    public function buildTokenURL(
        FileStream $stream,
        string $filename,
        Disposition $disposition,
        int $user_id,
        int $valid_for_at_least_hours
    ): URI {
        // a new DateTimeImmutable which is set to the end of now + $valid_for_at_least_hours hours
        $until = new \DateTimeImmutable(
            (new \DateTimeImmutable("now +$valid_for_at_least_hours hours"))->format('Y-m-d H:00')
        );

        $token = $this->data_signer->getSignedStreamToken(
            $stream,
            $filename,
            $disposition,
            $user_id,
            $until
        );
        return new URI(
            rtrim(ILIAS_HTTP_PATH, '/') . self::DELIVERY_ENDPOINT . $token
        );
    }
}
