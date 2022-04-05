<?php
declare(strict_types=1);

use ILIAS\FileDelivery\FileDeliveryTypes\DeliveryMethod;
use ILIAS\FileDelivery\Delivery;
use ILIAS\FileDelivery\HttpServiceAware;
use ILIAS\FileDelivery\ilFileDeliveryService;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    private Delivery $delivery;

    /**
     * ilFileDelivery constructor.
     *
     * @param string $file_path
     */
    public function __construct(string $file_path)
    {
        $this->delivery = new Delivery($file_path, self::http());
    }

    public static function deliverFileAttached(
        string $path_to_file,
        ?string $download_file_name = null,
        ?string $mime_type = null,
        bool $delete_file = false
    ) : void {
        $obj = new Delivery($path_to_file, self::http());

        if ($download_file_name !== null) {
            $obj->setDownloadFileName($download_file_name);
        }
        if ($mime_type !== null) {
            $obj->setMimeType($mime_type);
        }
        $obj->setDisposition(self::DISP_ATTACHMENT);
        $obj->setDeleteFile($delete_file);
        $obj->deliver();
    }

    public static function streamVideoInline(
        string $path_to_file,
        ?string $download_file_name = null
    ) : void {
        $obj = new Delivery($path_to_file, self::http());
        if ($download_file_name !== null) {
            $obj->setDownloadFileName($download_file_name);
        }
        $obj->setDisposition(self::DISP_INLINE);
        $obj->stream();
    }

    public static function deliverFileInline(
        string $path_to_file,
        ?string $download_file_name = null
    ) : void {
        $obj = new Delivery($path_to_file, self::http());
        if ($download_file_name !== null) {
            $obj->setDownloadFileName($download_file_name);
        }
        $obj->setDisposition(self::DISP_INLINE);
        $obj->deliver();
    }

    public static function returnASCIIFileName(string $original_filename) : string
    {
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
    public function __call(string $name, array $arguments)
    {
        throw new LogicException('');
    }

    /**
     * @deprecated
     */
    public static function deliverFileLegacy(
        string $a_file,
        ?string $a_filename = null,
        ?string $a_mime = null,
        ?bool $isInline = false,
        ?bool $removeAfterDelivery = false,
        ?bool $a_exit_after = true
    ) : void {
        global $DIC;
        // should we fail silently?
        if (!file_exists($a_file)) {
            return;
        }
        $delivery = new Delivery($a_file, $DIC->http());

        if ($isInline) {
            $delivery->setDisposition(self::DISP_INLINE);
        } else {
            $delivery->setDisposition(self::DISP_ATTACHMENT);
        }

        if ($a_mime !== null && $a_mime !== '') {
            $delivery->setMimeType($a_mime);
        }

        $delivery->setDownloadFileName($a_filename);
        $delivery->setConvertFileNameToAsci((bool) !$DIC->clientIni()->readVariable('file_access', 'disable_ascii'));
        $delivery->setDeleteFile($removeAfterDelivery);
        $delivery->setExitAfter($a_exit_after);
        $delivery->deliver();
    }
}
