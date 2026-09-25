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

namespace ILIAS\FileDelivery\Delivery\ResponseBuilder;

use ILIAS\FileDelivery\Setup\DeliveryMethodObjective;

/**
 * Builds the ResponseBuilder an installation is set up for.
 *
 * Which delivery method is used is decided by the setup and stored in a static
 * PHP artefact, so the runtime can pick its builder without a DB dependency.
 * A missing, unreadable or malformed artefact keeps the installation on PHP
 * delivery, which works everywhere.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ResponseBuilderFactory
{
    /**
     * @param string|null $path only passed in tests; defaults to the artefact
     *                          location the setup writes to
     */
    public static function fromArtefact(?string $path = null): ResponseBuilder
    {
        $path ??= DeliveryMethodObjective::PATH();
        if (!is_file($path)) {
            return new PHPResponseBuilder();
        }

        $settings = @include $path;

        return self::fromArray(is_array($settings) ? $settings : []);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function fromArray(array $settings): ResponseBuilder
    {
        return match ($settings[DeliveryMethodObjective::SETTINGS] ?? null) {
            DeliveryMethodObjective::XACCEL => self::xAccel($settings),
            DeliveryMethodObjective::XSENDFILE => new XSendFileResponseBuilder(),
            default => new PHPResponseBuilder(),
        };
    }

    /**
     * X-Accel needs the external data directory the web server maps its
     * internal location to. Without it the builder cannot be constructed, so an
     * incomplete artefact falls back to PHP delivery instead of failing on
     * every single download.
     *
     * @param array<string, mixed> $settings
     */
    private static function xAccel(array $settings): ResponseBuilder
    {
        $external_data_dir = $settings[DeliveryMethodObjective::SETTINGS_EXTERNAL_DATA_DIR] ?? null;

        if (!is_string($external_data_dir) || $external_data_dir === '') {
            return new PHPResponseBuilder();
        }

        return new XAccelResponseBuilder($external_data_dir);
    }
}
