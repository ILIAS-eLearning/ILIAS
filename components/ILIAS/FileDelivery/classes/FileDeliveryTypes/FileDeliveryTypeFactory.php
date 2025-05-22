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

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\Services;

/**
 * Class FileDeliveryTypeFactory
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @Internal
 */
final class FileDeliveryTypeFactory
{
    /**
     * @var ilFileDeliveryType[]
     */
    private static array $instances = [];


    /**
     * FileDeliveryTypeFactory constructor.
     *
     * @param Services $http
     */
    public function __construct(private Services $http)
    {
    }


    /**
     * Creates a new instance of the requested file delivery type.
     *
     * Please check the DeliveryMethod interface for the possible options.
     *
     *
     * @throws \ilException If the file delivery type is unknown.
     *
     * @see DeliveryMethod
     */
    public function getInstance(string $type): ilFileDeliveryType
    {
        assert(is_string($type));
        if (isset(self::$instances[$type])) {
            return self::$instances[$type];
        }
        self::$instances[$type] = match ($type) {
            DeliveryMethod::PHP => new PHP($this->http),
            DeliveryMethod::XSENDFILE => new XSendfile($this->http),
            DeliveryMethod::XACCEL => new XAccel($this->http),
            DeliveryMethod::PHP_CHUNKED => new PHPChunked($this->http),
            default => throw new \ilException("Unknown file delivery type \"$type\""),
        };

        return self::$instances[$type];
    }
}
