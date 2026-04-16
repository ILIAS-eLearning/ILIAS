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

namespace ILIAS;

use ILIAS\Component\Component;
use ILIAS\Setup\Agent;
use ILIAS\Refinery\Factory;
use ILIAS\Component\Resource\PublicAsset;
use ILIAS\Component\Resource\Endpoint;
use ILIAS\Component\EntryPoint;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\FileDelivery\Services;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Setup\DeliveryMethodObjective;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\XAccelResponseBuilder;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\XSendFileResponseBuilder;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\PHPResponseBuilder;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Setup\KeyRotationObjective;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\FileDelivery\Delivery\StreamDelivery;
use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\FileDelivery\FileDeliveryServices;
use ILIAS\FileDelivery\Token\DataSigning;

class FileDelivery implements Component
{
    public function init(
        array|\ArrayAccess &$define,
        array|\ArrayAccess &$implement,
        array|\ArrayAccess &$use,
        array|\ArrayAccess &$contribute,
        array|\ArrayAccess &$seek,
        array|\ArrayAccess &$provide,
        array|\ArrayAccess &$pull,
        array|\ArrayAccess &$internal,
    ): void {
        $define[] = DataSigning::class;
        $define[] = FileDeliveryServices::class;

        $contribute[Agent::class] = static fn(): \ILIAS\FileDelivery\Setup\Agent => new \ILIAS\FileDelivery\Setup\Agent(
            $pull[Factory::class]
        );

        $contribute[PublicAsset::class] = fn(): Endpoint => new Endpoint($this, "deliver.php");

        // INITIALIZATION OF SERVICES
        $internal[ResponseBuilder::class] = static function (): ResponseBuilder {
            $settings = (@include DeliveryMethodObjective::PATH()) ?? [];

            return match ($settings[DeliveryMethodObjective::SETTINGS] ?? null) {
                DeliveryMethodObjective::XACCEL => new XAccelResponseBuilder(),
                DeliveryMethodObjective::XSENDFILE => new XSendFileResponseBuilder(),
                default => new PHPResponseBuilder(),
            };
        };

        $internal[PHPResponseBuilder::class] = (static fn(): ResponseBuilder => new PHPResponseBuilder());

        $internal[DataSigning::class] = static function (): DataSigning {
            $key_strings = (array) ((@include KeyRotationObjective::PATH()) ?? []);
            $keys = array_map(
                static fn(string $key): SecretKey => new SecretKey($key),
                $key_strings
            );

            $current_key = array_shift($keys);

            return new DataSigner(
                new SecretKeyRotation(
                    $current_key,
                    ...$keys
                )
            );
        };

        $implement[DataSigning::class] = static fn(): DataSigning => $internal[DataSigning::class];

        $internal[StreamDelivery::class] = (static fn(): StreamDelivery => new StreamDelivery(
            $use[DataSigning::class],
            $use[GlobalHttpState::class],
            $internal[ResponseBuilder::class],
            $internal[PHPResponseBuilder::class],
        ));

        $internal[LegacyDelivery::class] = (static fn(): LegacyDelivery => new LegacyDelivery(
            $use[GlobalHttpState::class],
            $internal[ResponseBuilder::class],
            $internal[PHPResponseBuilder::class],
        ));

        $internal[FileDeliveryServices::class] = (static fn(): Services => new Services(
            $internal[StreamDelivery::class],
            $internal[LegacyDelivery::class],
            $use[DataSigning::class],
            $use[GlobalHttpState::class],
        ));

        $implement[FileDeliveryServices::class] = static fn(): FileDeliveryServices => $internal[FileDeliveryServices::class];

        $contribute[EntryPoint::class] = static fn(): EntryPoint => new \ILIAS\FileDelivery\EntryPoint(
            $internal[FileDeliveryServices::class],
            $use[GlobalHttpState::class],
        );
    }
}
