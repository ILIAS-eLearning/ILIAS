<?php

namespace ILIAS\FileDelivery\FileDeliveryTypes;

require_once './Services/FileDelivery/classes/HttpServiceAware.php';
require_once './Services/FileDelivery/classes/FileDeliveryTypes/DeliveryMethod.php';
require_once './Services/FileDelivery/classes/FileDeliveryTypes/HeaderBasedDeliveryHelper.php';

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\GlobalHttpState;

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
    private static $instances = array();
    /**
     * @var GlobalHttpState $http
     */
    private $http;


    /**
     * FileDeliveryTypeFactory constructor.
     *
     * @param GlobalHttpState $http
     */
    public function __construct(GlobalHttpState $http)
    {
        $this->http = $http;
    }


    /**
     * Creates a new instance of the requested file delivery type.
     *
     * Please check the DeliveryMethod interface for the possible options.
     *
     * @param string $type
     *
     * @return ilFileDeliveryType
     * @throws \ilException If the file delivery type is unknown.
     *
     * @see DeliveryMethod
     */
    public function getInstance($type)
    {
        assert(is_string($type));
        if (isset(self::$instances[$type])) {
            return self::$instances[$type];
        }
        switch ($type) {
            case DeliveryMethod::PHP:
                require_once('PHP.php');
                self::$instances[$type] = new PHP($this->http);
                break;
            case DeliveryMethod::XSENDFILE:
                require_once('XSendfile.php');
                self::$instances[$type] = new XSendfile($this->http);
                break;
            case DeliveryMethod::XACCEL:
                require_once('XAccel.php');
                self::$instances[$type] = new XAccel($this->http);
                break;
            case DeliveryMethod::PHP_CHUNKED:
                require_once('PHPChunked.php');
                self::$instances[$type] = new PHPChunked($this->http);
                break;
            default:
                throw new \ilException("Unknown file delivery type \"$type\"");
        }

        return self::$instances[$type];
    }
}
