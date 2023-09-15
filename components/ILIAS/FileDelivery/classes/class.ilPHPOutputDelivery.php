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

use ILIAS\FileDelivery\Delivery;
use ILIAS\FileDelivery\HttpServiceAware;
use ILIAS\FileUpload\MimeType;
use ILIAS\Modules\File\Settings\General;

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
        $settings = new General();
        $this->ilFileDelivery = new Delivery(ilFileDelivery::DIRECT_PHP_OUTPUT, self::http());
        $this->ilFileDelivery->setMimeType($mime_type);
        $this->ilFileDelivery->setDownloadFileName($download_file_name);
        $this->ilFileDelivery->setDisposition(ilFileDelivery::DISP_ATTACHMENT);
        $this->ilFileDelivery->setConvertFileNameToAsci($settings->isDownloadWithAsciiFileName());
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
