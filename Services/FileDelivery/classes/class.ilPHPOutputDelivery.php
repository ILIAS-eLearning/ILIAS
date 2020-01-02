<?php
require_once('class.ilFileDelivery.php');
require_once('Delivery.php');
require_once './Services/FileDelivery/classes/HttpServiceAware.php';

use ILIAS\FileDelivery\Delivery as Delivery;
use ILIAS\FileDelivery\HttpServiceAware;

/**
 * Class ilPHPOutputDelivery
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 *
 * @public
 */
final class ilPHPOutputDelivery
{
    use HttpServiceAware;
    /**
     * @var ILIAS\FileDelivery\Delivery
     */
    protected $ilFileDelivery;


    /**
     * @param        $download_file_name
     * @param string $mime_type
     */
    public function start($download_file_name, $mime_type = ilMimeTypeUtil::APPLICATION__OCTET_STREAM)
    {
        global $DIC;
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $this->ilFileDelivery = new Delivery(ilFileDelivery::DIRECT_PHP_OUTPUT, self::http());
        $this->ilFileDelivery->setMimeType($mime_type);
        $this->ilFileDelivery->setDownloadFileName($download_file_name);
        $this->ilFileDelivery->setDisposition(ilFileDelivery::DISP_ATTACHMENT);
        $this->ilFileDelivery->setConvertFileNameToAsci((bool) !$ilClientIniFile->readVariable('file_access', 'disable_ascii'));
        $this->ilFileDelivery->clearBuffer();
        $this->ilFileDelivery->checkCache();
        $this->ilFileDelivery->setGeneralHeaders();
        $this->ilFileDelivery->setShowLastModified(false);
        $this->ilFileDelivery->setCachingHeaders();
    }


    public function stop()
    {
        $this->ilFileDelivery->close();
    }
}
