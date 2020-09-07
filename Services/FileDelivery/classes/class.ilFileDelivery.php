<?php
require_once('./Services/FileDelivery/classes/FileDeliveryTypes/FileDeliveryTypeFactory.php');
require_once './Services/FileDelivery/classes/FileDeliveryTypes/DeliveryMethod.php';
require_once('./Services/FileDelivery/classes/Delivery.php');
require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryService.php');
require_once './Services/FileDelivery/classes/HttpServiceAware.php';

use ILIAS\FileDelivery\FileDeliveryTypes\DeliveryMethod;
use ILIAS\FileDelivery\Delivery;
use ILIAS\FileDelivery\HttpServiceAware;
use ILIAS\FileDelivery\ilFileDeliveryService;

/**
 * Class ilFileDelivery
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 *
 * @public
 */
final class ilFileDelivery implements ilFileDeliveryService
{
    use HttpServiceAware;
    const DIRECT_PHP_OUTPUT = Delivery::DIRECT_PHP_OUTPUT;
    const DELIVERY_METHOD_XSENDFILE = DeliveryMethod::XSENDFILE;
    const DELIVERY_METHOD_XACCEL = DeliveryMethod::XACCEL;
    const DELIVERY_METHOD_PHP = DeliveryMethod::PHP;
    const DELIVERY_METHOD_PHP_CHUNKED = DeliveryMethod::PHP_CHUNKED;
    const DISP_ATTACHMENT = Delivery::DISP_ATTACHMENT;
    const DISP_INLINE = Delivery::DISP_INLINE;
    /**
     * @var Delivery $delivery
     */
    private $delivery;


    /**
     * ilFileDelivery constructor.
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        assert(is_string($filePath));
        $this->delivery = new Delivery($filePath, self::http());
    }


    /**
     * @inheritdoc
     */
    public static function deliverFileAttached($path_to_file, $download_file_name = '', $mime_type = '', $delete_file = false)
    {
        assert(is_string($path_to_file));
        assert(is_string($download_file_name));
        assert(is_string($mime_type));
        assert(is_bool($delete_file));

        $obj = new Delivery($path_to_file, self::http());

        if (self::isNonEmptyString($download_file_name)) {
            $obj->setDownloadFileName($download_file_name);
        }
        if (self::isNonEmptyString($mime_type)) {
            $obj->setMimeType($mime_type);
        }
        $obj->setDisposition(self::DISP_ATTACHMENT);
        $obj->setDeleteFile($delete_file);
        $obj->deliver();
    }


    /**
     * @inheritdoc
     */
    public static function streamVideoInline($path_to_file, $download_file_name = '')
    {
        assert(is_string($path_to_file));
        assert(is_string($download_file_name));
        $obj = new Delivery($path_to_file, self::http());
        if (self::isNonEmptyString($download_file_name)) {
            $obj->setDownloadFileName($download_file_name);
        }
        $obj->setDisposition(self::DISP_INLINE);
        $obj->stream();
    }


    /**
     * @inheritdoc
     */
    public static function deliverFileInline($path_to_file, $download_file_name = '')
    {
        assert(is_string($path_to_file));
        assert(is_string($download_file_name));
        $obj = new Delivery($path_to_file, self::http());

        if (self::isNonEmptyString($download_file_name)) {
            $obj->setDownloadFileName($download_file_name);
        }
        $obj->setDisposition(self::DISP_INLINE);
        $obj->deliver();
    }


    /**
     * @inheritdoc
     */
    public static function returnASCIIFileName($original_filename)
    {
        assert(is_string($original_filename));

        return Delivery::returnASCIIFileName($original_filename);
    }


    /**
     * Workaround because legacy components try to call methods which are moved to the Deliver
     * class.
     *
     * @param string $name      The function name which was not found on the current object.
     * @param array  $arguments The function arguments passed to the function which was not existent
     *                          on the current object.
     */
    public function __call($name, array $arguments)
    {
        assert(is_string($name));
        //forward call to Deliver class
        call_user_func_array([ $this->delivery, $name ], $arguments);
    }


    /**
     * Checks if the string is not empty.
     *
     * @param string $text The text which should be checked.
     *
     * @return bool True if the text is not empty otherwise false.
     */
    private static function isNonEmptyString($text)
    {
        assert(is_string($text));

        return (bool) strcmp($text, '') !== 0;
    }
}
