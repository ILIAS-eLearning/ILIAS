<?php

declare(strict_types=1);

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\Services;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    private static array $instances = array();
    private \ILIAS\HTTP\Services $http;


    /**
     * FileDeliveryTypeFactory constructor.
     *
     * @param Services $http
     */
    public function __construct(Services $http)
    {
        $this->http = $http;
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
    public function getInstance(string $type): \ILIAS\FileDelivery\ilFileDeliveryType
    {
        assert(is_string($type));
        if (isset(self::$instances[$type])) {
            return self::$instances[$type];
        }
        switch ($type) {
            case DeliveryMethod::PHP:
                self::$instances[$type] = new PHP($this->http);
                break;
            case DeliveryMethod::XSENDFILE:
                self::$instances[$type] = new XSendfile($this->http);
                break;
            case DeliveryMethod::XACCEL:
                self::$instances[$type] = new XAccel($this->http);
                break;
            case DeliveryMethod::PHP_CHUNKED:
                self::$instances[$type] = new PHPChunked($this->http);
                break;
            default:
                throw new \ilException("Unknown file delivery type \"$type\"");
        }

        return self::$instances[$type];
    }
}
