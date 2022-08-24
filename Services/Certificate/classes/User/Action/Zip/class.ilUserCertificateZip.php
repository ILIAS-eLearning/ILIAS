<?php

declare(strict_types=1);

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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateZip
{
    private int $objectId;
    private string $webDirectory;
    private string $certificatePath;
    private string $typeInFileName;
    private string $installionId;

    public function __construct(
        int $objectId,
        string $certificatePath,
        string $webDirectory = CLIENT_WEB_DIR,
        string $installationId = IL_INST_ID
    ) {
        $this->objectId = $objectId;
        $this->certificatePath = $certificatePath;
        $this->webDirectory = $webDirectory;
        $this->installionId = $installationId;

        // The mapping to types is made to reflect the old behaviour of
        // the adapters
        $iliasType = ilObject::_lookupType($this->objectId);

        $typeInFileName = 'not_defined';
        if ('crs' === $iliasType) {
            $typeInFileName = 'course';
        } elseif ('tst' === $iliasType) {
            $typeInFileName = 'test';
        } elseif ('exc' === $iliasType) {
            $typeInFileName = 'exc';
        } elseif ('sahs' === $iliasType) {
            $typeInFileName = 'scorm';
        }

        $this->typeInFileName = $typeInFileName;
    }

    public function createArchiveDirectory(): string
    {
        $type = ilObject::_lookupType($this->objectId);
        $certificateId = $this->objectId;

        $directory = $this->webDirectory . $this->certificatePath . time() . '__' . $this->installionId . '__' . $type . '__' . $certificateId . '__certificate/';
        ilFileUtils::makeDirParents($directory);

        return $directory;
    }

    /**
     * Adds PDF data as a file to a given directory
     * @param string $pdfdata  Binary PDF data
     * @param string $dir      Directory to contain the PDF data
     * @param string $filename The filename to save the PDF data
     */
    public function addPDFtoArchiveDirectory(string $pdfdata, string $dir, string $filename): void
    {
        $fh = fopen($dir . $filename, 'wb');
        fwrite($fh, $pdfdata);
        fclose($fh);
    }

    public function zipCertificatesInArchiveDirectory(string $dir, bool $deliver = true): string
    {
        $zipFile = time() . '__' . $this->installionId . '__' . $this->typeInFileName . '__' . $this->objectId . '__certificates.zip';
        $zipFilePath = $this->webDirectory . $this->certificatePath . $zipFile;

        ilFileUtils::zip($dir, $zipFilePath);
        ilFileUtils::delDir($dir);

        if ($deliver) {
            ilFileDelivery::deliverFileLegacy($zipFilePath, $zipFile, 'application/zip', false, true);
        }

        return $zipFilePath;
    }
}
