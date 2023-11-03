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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Init
{
    public static function init(Container $c): void
    {
        $c['file_delivery.response_builder'] = static function (): ResponseBuilder {
            $settings = (require DeliveryMethodObjective::ARTIFACT) ?? [];

            switch ($settings[DeliveryMethodObjective::SETTINGS] ?? null) {
                case DeliveryMethodObjective::XSENDFILE:
                    return new XSendFileResponseBuilder();
                case DeliveryMethodObjective::PHP:
                default:
                    return new PHPResponseBuilder();
            }
        };

        $c['file_delivery.data_signer'] = static function (): DataSigner {
            $keys = array_map(static function (string $key): SecretKey {
                return new SecretKey($key);
            }, (require KeyRotationObjective::KEY_ROTATION) ?? []);

            $current_key = array_shift($keys);

            return new DataSigner(
                new SecretKeyRotation(
                    $current_key,
                    ...$keys
                )
            );
        };

        $c['file_delivery.delivery'] = static function () use ($c): \ILIAS\FileDelivery\Delivery\StreamDelivery {
            // if http is not initialized, we need to do it here
            if (!$c->offsetExists('http')) {
                $init_http = new \InitHttpServices();
                $init_http->init($c);
            }

            return new \ILIAS\FileDelivery\Delivery\StreamDelivery(
                $c['file_delivery.data_signer'],
                $c['http'],
                $c['file_delivery.response_builder']
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
                $c['file_delivery.response_builder']
            );
        };

        $c['file_delivery'] = static function () use ($c): Services {
            return new Services(
                $c['file_delivery.delivery'],
                $c['file_delivery.legacy_delivery'],
                $c['file_delivery.data_signer']
            );
        };
    }
}
