<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateZip
{

    /**
     * @var int
     */
    private $objectId;

    /**
     * @var string
     */
    private $webDirectory;

    /**
     * @var string
     */
    private $certificatePath;

    /**
     * @var string
     */
    private $typeInFileName;

    /**
     * @var string
     */
    private $installionId;

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

    /**
     * Creates a directory for a zip archive containing multiple certificates
     *
     * @return string The created archive directory
     */
    public function createArchiveDirectory()
    {
        $type = ilObject::_lookupType($this->objectId);
        $certificateId = $this->objectId;

        $directory = $this->webDirectory . $this->certificatePath . time() . '__' . $this->installionId . '__' . $type . '__' . $certificateId . '__certificate/';
        ilUtil::makeDirParents($directory);

        return $directory;
    }

    /**
     * Adds PDF data as a file to a given directory
     *
     * @param binary $pdfdata Binary PDF data
     * @param string $dir Directory to contain the PDF data
     * @param string $filename The filename to save the PDF data
     */
    public function addPDFtoArchiveDirectory($pdfdata, $dir, $filename)
    {
        $fh = fopen($dir . $filename, 'wb');
        fwrite($fh, $pdfdata);
        fclose($fh);
    }

    /**
     * Create a ZIP file from a directory with certificates
     *
     * @param string $dir Directory containing the certificates
     * @param boolean $deliver TRUE to deliver the ZIP file, FALSE to return the filename only
     * @return string The created ZIP archive path
     */
    public function zipCertificatesInArchiveDirectory($dir, $deliver = true)
    {
        $zipFile = time() . '__' . $this->installionId . '__' . $this->typeInFileName . '__' . $this->objectId . '__certificates.zip';
        $zipFilePath = $this->webDirectory . $this->certificatePath . $zipFile;

        ilUtil::zip($dir, $zipFilePath);
        ilUtil::delDir($dir);

        if ($deliver) {
            ilUtil::deliverFile($zipFilePath, $zipFile, 'application/zip', false, true);
        }

        return $zipFilePath;
    }
}
