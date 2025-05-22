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

use ILIAS\FileDelivery\Delivery\StreamDelivery;
use ILIAS\DI\Container;
use ILIAS\FileDelivery\Setup\KeyRotationObjective;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\XSendFileResponseBuilder;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\PHPResponseBuilder;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Setup\DeliveryMethodObjective;
use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\XAccelResponseBuilder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Init
{
    public static function init(Container $c): void
    {
        $c['file_delivery.response_builder'] = static function (): ResponseBuilder {
            $settings = (@include DeliveryMethodObjective::PATH()) ?? [];

            return match ($settings[DeliveryMethodObjective::SETTINGS] ?? null) {
                DeliveryMethodObjective::XACCEL => new XAccelResponseBuilder(),
                DeliveryMethodObjective::XSENDFILE => new XSendFileResponseBuilder(),
                default => new PHPResponseBuilder(),
            };
        };

        $c['file_delivery.fallback_response_builder'] = (static fn(): ResponseBuilder => new PHPResponseBuilder());

        $c['file_delivery.data_signer'] = static function (): DataSigner {
            $keys = array_map(static fn(string $key): SecretKey => new SecretKey($key), (require KeyRotationObjective::PATH()) ?? []);

            $current_key = array_shift($keys);

            return new DataSigner(
                new SecretKeyRotation(
                    $current_key,
                    ...$keys
                )
            );
        };

        $c['file_delivery.delivery'] = static function () use ($c): StreamDelivery {
            // if http is not initialized, we need to do it here
            if (!$c->offsetExists('http')) {
                $init_http = new \InitHttpServices();
                $init_http->init($c);
            }

            return new StreamDelivery(
                $c['file_delivery.data_signer'],
                $c['http'],
                $c['file_delivery.response_builder'],
                $c['file_delivery.fallback_response_builder']
            );
        };

        $c['file_delivery.legacy_delivery'] = static function () use ($c): LegacyDelivery {
            // if http is not initialized, we need to do it here
            if (!$c->offsetExists('http')) {
                $init_http = new \InitHttpServices();
                $init_http->init($c);
            }

            return new LegacyDelivery(
                $c['http'],
                $c['file_delivery.response_builder'],
                $c['file_delivery.fallback_response_builder']
            );
        };

        $c['file_delivery'] = (static fn(): Services => new Services(
            $c['file_delivery.delivery'],
            $c['file_delivery.legacy_delivery'],
            $c['file_delivery.data_signer'],
            $c['http']
        ));
    }
}
