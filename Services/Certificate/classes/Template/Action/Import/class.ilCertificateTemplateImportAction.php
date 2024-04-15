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
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateImportAction
{
    private readonly ilCertificateTemplateRepository $templateRepository;
    private readonly ilCertificateObjectHelper $objectHelper;
    private readonly ilCertificateUtilHelper $utilHelper;
    private readonly ilCertificateBackgroundImageFileService $fileService;

    public function __construct(
        private readonly int $objectId,
        private readonly string $certificatePath,
        private readonly ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilLogger $logger,
        private readonly Filesystem $filesystem,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilDBInterface $database = null,
        ?ilCertificateBackgroundImageFileService $fileService = null
    ) {
        if (null === $database) {
            global $DIC;
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

        if (null === $fileService) {
            $fileService = new ilCertificateBackgroundImageFileService(
                $certificatePath,
                $filesystem
            );
        }
        $this->fileService = $fileService;
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
        string $iliasVerision = ILIAS_VERSION_NUMERIC,
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
        $backgroundImagePath = $certificate->getBackgroundImagePath();
        $cardThumbnailImagePath = $certificate->getThumbnailImagePath();
        $xsl = $certificate->getCertificateContent();

        foreach ($contents as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if (str_contains($file->getPath(), '.xml')) {
                $xsl = $this->filesystem->read($file->getPath());
                // as long as we cannot make RPC calls in a given directory, we have
                // to add the complete path to every url
                $xsl = preg_replace_callback(
                    "/url\([']{0,1}(.*?)[']{0,1}\)/",
                    function (array $matches) use ($rootDir): string {
                        $basePath = rtrim(dirname($this->fileService->getBackgroundImageDirectory($rootDir)), '/');
                        $fileName = basename($matches[1]);

                        if ('[BACKGROUND_IMAGE]' === $fileName) {
                            $basePath = '';
                        } elseif ($basePath !== '') {
                            $basePath .= '/';
                        }

                        return 'url(' . $basePath . $fileName . ')';
                    },
                    $xsl
                );
            } elseif (str_contains($file->getPath(), '.jpg')) {
                $newBackgroundImageName = 'background_' . $newVersion . '.jpg';
                $newPath = $this->certificatePath . $newBackgroundImageName;
                $this->filesystem->copy($file->getPath(), $newPath);

                $backgroundImagePath = $this->certificatePath . $newBackgroundImageName;
                // upload of the background image, create a thumbnail

                $backgroundImageThumbPath = $this->getBackgroundImageThumbnailPath();

                $thumbnailImagePath = $rootDir . $backgroundImageThumbPath;

                $originalImagePath = $rootDir . $newPath;
                $this->utilHelper->convertImage(
                    $originalImagePath,
                    $thumbnailImagePath,
                    '100'
                );
            } elseif (str_contains($file->getPath(), '.svg')) {
                $newCardThumbnailName = 'thumbnail_' . $newVersion . '.svg';
                $newPath = $this->certificatePath . $newCardThumbnailName;

                $this->filesystem->copy($file->getPath(), $newPath);

                $cardThumbnailImagePath = $this->certificatePath . $newCardThumbnailName;
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
                $backgroundImagePath,
                $jsonEncodedTemplateValues,
                $cardThumbnailImagePath
            ])
        );

        $template = new ilCertificateTemplate(
            $this->objectId,
            $this->objectHelper->lookupType($this->objectId),
            $xsl,
            $newHashValue,
            $jsonEncodedTemplateValues,
            $newVersion,
            $iliasVerision,
            time(),
            false,
            $backgroundImagePath,
            $cardThumbnailImagePath
        );

        $this->templateRepository->save($template);

        $clean_up_import_dir();

        return true;
    }

    /**
     * Creates a directory for a zip archive containing multiple certificates
     * @return string The created archive directory
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

    private function getBackgroundImageThumbnailPath(): string
    {
        return $this->certificatePath . 'background.jpg.thumb.jpg';
    }
}
