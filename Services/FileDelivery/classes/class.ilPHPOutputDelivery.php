<?php

declare(strict_types=1);

use ILIAS\FileDelivery\Delivery;
use ILIAS\FileDelivery\HttpServiceAware;
use ILIAS\FileUpload\MimeType;

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
    protected ?Delivery $ilFileDelivery = null;


    /**
     * @param        $download_file_name
     */
    public function start(string $download_file_name, string $mime_type = MimeType::APPLICATION__OCTET_STREAM): void
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


    public function stop(): void
    {
        $this->ilFileDelivery->close();
    }
}
