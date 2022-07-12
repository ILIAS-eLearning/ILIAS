<?php declare(strict_types=1);

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

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateImportAction
{
    private int $objectId;
    private string $certificatePath;
    private ilCertificateTemplateRepository $templateRepository;
    private ilCertificatePlaceholderDescription $placeholderDescriptionObject;
    private ilLogger $logger;
    private Filesystem $filesystem;
    private ilCertificateObjectHelper $objectHelper;
    private ilCertificateUtilHelper $utilHelper;
    private string $installationID;
    private ilCertificateBackgroundImageFileService $fileService;

    public function __construct(
        int $objectId,
        string $certificatePath,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilLogger $logger,
        Filesystem $filesystem,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilDBInterface $database = null,
        ?ilCertificateBackgroundImageFileService $fileService = null
    ) {
        $this->objectId = $objectId;
        $this->certificatePath = $certificatePath;

        $this->logger = $logger;
        if (null === $database) {
            global $DIC;
            $database = $DIC->database();
        }

        $this->filesystem = $filesystem;

        $this->placeholderDescriptionObject = $placeholderDescriptionObject;

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
     * @param string       $zipFile
     * @param string       $filename
     * @param string       $rootDir
     * @param string       $iliasVerision
     * @param string|false $installationID
     * @return bool
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
    ) : bool {
        $importPath = $this->createArchiveDirectory($installationID);

        $result = $this->utilHelper->moveUploadedFile($zipFile, $filename, $rootDir . $importPath . $filename);

        if (!$result) {
            $this->filesystem->deleteDir($importPath);
            return false;
        }

        $this->utilHelper->unzip(
            $rootDir . $importPath . $filename,
            true
        );

        $subDirectoryName = str_replace('.zip', '', strtolower($filename)) . '/';
        $subDirectoryAbsolutePath = $rootDir . $importPath . $subDirectoryName;

        $copyDirectory = $importPath;
        if (is_dir($subDirectoryAbsolutePath)) {
            $copyDirectory = $subDirectoryAbsolutePath;
        }

        $directoryInformation = $this->utilHelper->getDir($copyDirectory);

        $xmlFiles = 0;
        foreach ($directoryInformation as $file) {
            if (strcmp($file['type'], 'file') === 0 && strpos($file['entry'], '.xml') !== false) {
                $xmlFiles++;
            }
        }

        if (0 === $xmlFiles) {
            $this->filesystem->deleteDir($importPath);
            return false;
        }

        $certificate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

        $currentVersion = $certificate->getVersion();
        $newVersion = $currentVersion + 1;
        $backgroundImagePath = $certificate->getBackgroundImagePath();
        $cardThumbnailImagePath = $certificate->getThumbnailImagePath();

        $xsl = $certificate->getCertificateContent();

        foreach ($directoryInformation as $file) {
            if (strcmp($file['type'], 'file') === 0) {
                $filePath = $importPath . $subDirectoryName . $file['entry'];
                if (strpos($file['entry'], '.xml') !== false) {
                    $xsl = $this->filesystem->read($filePath);
                    // as long as we cannot make RPC calls in a given directory, we have
                    // to add the complete path to every url
                    $xsl = preg_replace_callback(
                        "/url\([']{0,1}(.*?)[']{0,1}\)/",
                        function (array $matches) use ($rootDir) : string {
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
                } elseif (strpos($file['entry'], '.jpg') !== false) {
                    $newBackgroundImageName = 'background_' . $newVersion . '.jpg';
                    $newPath = $this->certificatePath . $newBackgroundImageName;
                    $this->filesystem->copy($filePath, $newPath);

                    $backgroundImagePath = $this->certificatePath . $newBackgroundImageName;
                    // upload of the background image, create a thumbnail

                    $backgroundImageThumbPath = $this->getBackgroundImageThumbnailPath();

                    $thumbnailImagePath = $rootDir . $backgroundImageThumbPath;

                    $originalImagePath = $rootDir . $newPath;
                    $this->utilHelper->convertImage(
                        $originalImagePath,
                        $thumbnailImagePath,
                        'JPEG',
                        "100"
                    );
                } elseif (strpos($file['entry'], '.svg') !== false) {
                    $newCardThumbnailName = 'thumbnail_' . $newVersion . '.svg';
                    $newPath = $this->certificatePath . $newCardThumbnailName;

                    $this->filesystem->copy($filePath, $newPath);

                    $cardThumbnailImagePath = $this->certificatePath . $newCardThumbnailName;
                }
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
            true,
            $backgroundImagePath,
            $cardThumbnailImagePath
        );

        $this->templateRepository->save($template);

        $this->utilHelper->delDir($importPath);

        return true;
    }

    /**
     * Creates a directory for a zip archive containing multiple certificates
     * @param string $installationID
     * @return string The created archive directory
     * @throws IOException
     */
    private function createArchiveDirectory(string $installationID) : string
    {
        $type = $this->objectHelper->lookupType($this->objectId);
        $certificateId = $this->objectId;

        $dir = $this->certificatePath . time() . '__' . $installationID . '__' . $type . '__' . $certificateId . '__certificate/';
        $this->filesystem->createDir($dir);

        return $dir;
    }

    private function getBackgroundImageThumbnailPath() : string
    {
        return $this->certificatePath . 'background.jpg.thumb.jpg';
    }
}
