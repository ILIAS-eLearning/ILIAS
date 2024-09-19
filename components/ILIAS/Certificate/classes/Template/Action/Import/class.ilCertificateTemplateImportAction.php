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

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Certificate\File\ilCertificateTemplateStakeholder;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateImportAction
{
    private readonly ilCertificateTemplateRepository $templateRepository;
    private readonly ilCertificateObjectHelper $objectHelper;
    private readonly ilCertificateUtilHelper $utilHelper;
    private readonly ilCertificateTemplateStakeholder $stakeholder;

    public function __construct(
        private readonly int $objectId,
        private readonly string $certificatePath,
        private readonly ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilLogger $logger,
        private readonly Filesystem $filesystem,
        private readonly IRSS $irss,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilDBInterface $database = null,
    ) {
        global $DIC;
        if (null === $database) {
            $database = $DIC->database();
        }

        if (null === $templateRepository) {
            $templateRepository = new ilCertificateTemplateDatabaseRepository($database, $logger);
        }
        $this->templateRepository = $templateRepository;

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $utilHelper) {
            $utilHelper = new ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;
        $this->stakeholder = new ilCertificateTemplateStakeholder();
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     */
    public function import(
        string $zipFile,
        string $filename,
        string $rootDir = CLIENT_WEB_DIR,
        string $iliasVersion = ILIAS_VERSION_NUMERIC,
        string $installationID = IL_INST_ID
    ): bool {
        $importPath = $this->createArchiveDirectory($installationID);

        $clean_up_import_dir = function () use (&$importPath) {
            if ($this->filesystem->hasDir($importPath)) {
                $this->filesystem->deleteDir($importPath);
            }
        };

        $result = $this->utilHelper->moveUploadedFile($zipFile, $filename, $rootDir . $importPath . $filename);
        if (!$result) {
            $clean_up_import_dir();

            return false;
        }

        $destination_dir = $rootDir . $importPath;
        $unzip = $this->utilHelper->unzip(
            $rootDir . $importPath . $filename,
            $destination_dir,
            true
        );

        $unzipped = $unzip->extract();
        if (!$unzipped) {
            $clean_up_import_dir();

            return false;
        }

        if ($this->filesystem->has($importPath . $filename)) {
            $this->filesystem->delete($importPath . $filename);
        }

        $xmlFiles = 0;
        $contents = $this->filesystem->listContents($importPath);
        foreach ($contents as $file) {
            if ($file->isFile() && str_contains($file->getPath(), '.xml')) {
                $xmlFiles++;
            }
        }

        if (0 === $xmlFiles) {
            return false;
        }

        $certificate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

        $currentVersion = $certificate->getVersion();
        $newVersion = $currentVersion + 1;
        $xsl = $certificate->getCertificateContent();

        foreach ($contents as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if (str_contains($file->getPath(), '.xml')) {
                $xsl = $this->filesystem->read($file->getPath());
                // TODO: wth??
            } elseif (str_contains($file->getPath(), '.jpg')) {
                $background_rid = $this->irss->manage()->stream(
                    $this->filesystem->readStream($file->getPath()),
                    $this->stakeholder
                );
            } elseif (str_contains($file->getPath(), '.svg')) {
                $card_thumbnail_rid = $this->irss->manage()->stream(
                    $this->filesystem->readStream($file->getPath()),
                    $this->stakeholder
                );
            }
        }

        $jsonEncodedTemplateValues = json_encode(
            $this->placeholderDescriptionObject->getPlaceholderDescriptions(),
            JSON_THROW_ON_ERROR
        );

        $newHashValue = hash(
            'sha256',
            implode('', [
                $xsl,
                isset($background_rid) ? $this->irss->manage()->getResource(
                    $background_rid
                )->getStorageID() : '',
                $jsonEncodedTemplateValues,
                isset($card_thumbnail_rid) ? $this->irss->manage()->getResource(
                    $card_thumbnail_rid
                )->getStorageID() : ''
            ])
        );

        $template = new ilCertificateTemplate(
            $this->objectId,
            $this->objectHelper->lookupType($this->objectId),
            $xsl,
            $newHashValue,
            $jsonEncodedTemplateValues,
            $newVersion,
            $iliasVersion,
            time(),
            false,
            isset($background_rid) ? $background_rid->serialize() : '',
            isset($card_thumbnail_rid) ? $card_thumbnail_rid->serialize() : ''
        );

        $this->templateRepository->save($template);

        $clean_up_import_dir();

        return true;
    }

    /**
     * Creates a directory for a zip archive containing multiple certificates
     * @return string      The created archive directory
     * @throws IOException
     */
    private function createArchiveDirectory(string $installationID): string
    {
        $type = $this->objectHelper->lookupType($this->objectId);
        $certificateId = $this->objectId;

        $dir = $this->certificatePath . time() . '__' . $installationID . '__' . $type . '__' . $certificateId . '__certificate/';
        if ($this->filesystem->hasDir($dir)) {
            $this->filesystem->deleteDir($dir);
        }
        $this->filesystem->createDir($dir);

        return $dir;
    }
}
