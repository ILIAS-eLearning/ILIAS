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
use ILIAS\FileUpload\Processor\SVGBlacklistPreProcessor;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;

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
    private Filesystem $web_fs;
    private Filesystem $tmp_fs;
    private ilCertificateObjectHelper $objectHelper;
    private ilCertificateUtilHelper $utilHelper;
    private string $installationID;
    private ilCertificateBackgroundImageFileService $fileService;
    private SVGBlacklistPreProcessor $svg_blacklist_processor;

    public function __construct(
        int $objectId,
        string $certificatePath,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilLogger $logger,
        Filesystem $filesystem,
        Filesystem $tmp_fs,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilDBInterface $database = null,
        ?ilCertificateBackgroundImageFileService $fileService = null,
        ?SVGBlacklistPreProcessor $svg_blacklist_processor = null
    ) {
        $this->objectId = $objectId;
        $this->certificatePath = $certificatePath;

        $this->logger = $logger;
        if (null === $database) {
            global $DIC;
            $database = $DIC->database();
        }

        $this->web_fs = $filesystem;
        $this->tmp_fs = $tmp_fs;

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
        if (null === $svg_blacklist_processor) {
            $svg_blacklist_processor = new SVGBlacklistPreProcessor();
        }
        $this->svg_blacklist_processor = $svg_blacklist_processor;
    }

    /**
     * @param string       $zipFile
     * @param string       $filename
     * @param string       $web_directory
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
        string $web_directory = CLIENT_WEB_DIR,
        string $storage_directory = CLIENT_DATA_DIR,
        string $iliasVerision = ILIAS_VERSION_NUMERIC,
        string $installationID = IL_INST_ID
    ): bool {
        $rel_tmp_import_path = $this->createTemporaryArchiveDirectory($installationID);
        $abs_tmp_directory = rtrim($storage_directory, '/') . '/temp/';
        $rel_import_path = $this->createArchiveDirectory($installationID);

        $clean_up_import_dir = function () use (&$rel_tmp_import_path, &$rel_import_path): void {
            try {
                if ($this->tmp_fs->hasDir($rel_tmp_import_path)) {
                    $this->tmp_fs->deleteDir($rel_tmp_import_path);
                }
            } catch (Throwable $e) {
                $this->logger->error(sprintf("Can't clean up temporary import directory: %s", $e->getMessage()));
                $this->logger->error($e->getTraceAsString());
            }

            try {
                if ($this->web_fs->hasDir($rel_import_path)) {
                    $this->web_fs->deleteDir($rel_import_path);
                }
            } catch (Throwable $e) {
                $this->logger->error(sprintf("Can't clean up import directory: %s", $e->getMessage()));
                $this->logger->error($e->getTraceAsString());
            }
        };

        try {
            $abs_zip_path = $abs_tmp_directory . $rel_tmp_import_path . $filename;
            $result = $this->utilHelper->moveUploadedFile(
                $zipFile,
                $filename,
                $abs_zip_path
            );

            if (!$result) {
                return false;
            }

            $this->utilHelper->unzip(
                $abs_tmp_directory . $rel_tmp_import_path . $filename,
                true
            );

            $abs_unzip_destination_dir = $abs_tmp_directory . $rel_tmp_import_path;
            $sub_directory = str_replace('.zip', '', strtolower($filename)) . '/';
            $abs_sub_directory_path = $abs_tmp_directory . $rel_tmp_import_path . $sub_directory;
            if (is_dir($abs_sub_directory_path)) {
                $abs_target_directory = $abs_sub_directory_path;
            }

            $this->utilHelper->renameExecutables($abs_sub_directory_path);

            if ($this->tmp_fs->has($rel_tmp_import_path . $filename)) {
                $this->tmp_fs->delete($rel_tmp_import_path . $filename);
            }

            $tmp_contents = $this->tmp_fs->listContents($rel_tmp_import_path, true);
            foreach ($tmp_contents as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                if (!$this->web_fs->has($rel_import_path . basename($file->getPath()))) {
                    $this->web_fs->writeStream(
                        $rel_import_path . basename($file->getPath()),
                        $this->tmp_fs->readStream($file->getPath())
                    );
                }
            }

            $num_background_images = 0;
            $num_tile_images = 0;
            $num_xml_files = 0;
            $contents = $this->web_fs->listContents($rel_import_path);
            foreach ($contents as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                if (strpos($file->getPath(), '.xml') !== false) {
                    ++$num_xml_files;
                }

                if (strpos($file->getPath(), '.svg') !== false) {
                    $stream = $this->web_fs->readStream($file->getPath());
                    $file_metadata = $stream->getMetadata();
                    $absolute_file_path = $file_metadata['uri'];

                    $metadata = new Metadata(
                        pathinfo($absolute_file_path)['basename'],
                        filesize($absolute_file_path),
                        mime_content_type($absolute_file_path)
                    );

                    ++$num_tile_images;

                    $processing_result = $this->svg_blacklist_processor->process($stream, $metadata);
                    if ($processing_result->getCode() !== ProcessingStatus::OK) {
                        return false;
                    }
                }

                if (str_contains($file->getPath(), '.jpg')) {
                    ++$num_background_images;
                }
            }

            if (0 === $num_xml_files) {
                $this->logger->error('No XML file found in the imported zip file');
                return false;
            }
            if ($num_background_images > 1) {
                $this->logger->error('More than one background image found in the imported zip file');
                return false;
            }
            if ($num_tile_images > 1) {
                $this->logger->error('More than one tile image found in the imported zip file');
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

                if (strpos($file->getPath(), '.xml') !== false) {
                    $xsl = $this->web_fs->read($file->getPath());
                    // as long as we cannot make RPC calls in a given directory, we have
                    // to add the complete path to every url
                    $xsl = preg_replace_callback(
                        "/url\([']{0,1}(.*?)[']{0,1}\)/",
                        function (array $matches) use ($web_directory): string {
                            $basePath = rtrim(
                                dirname($this->fileService->getBackgroundImageDirectory($web_directory)),
                                '/'
                            );
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
                } elseif (strpos($file->getPath(), '.jpg') !== false) {
                    $newBackgroundImageName = 'background_' . $newVersion . '.jpg';
                    $newPath = $this->certificatePath . $newBackgroundImageName;
                    $this->web_fs->copy($file->getPath(), $newPath);

                    $backgroundImagePath = $this->certificatePath . $newBackgroundImageName;
                    // upload of the background image, create a thumbnail

                    $backgroundImageThumbPath = $this->getBackgroundImageThumbnailPath();

                    $thumbnailImagePath = $web_directory . $backgroundImageThumbPath;

                    $originalImagePath = $web_directory . $newPath;
                    $this->utilHelper->convertImage(
                        $originalImagePath,
                        $thumbnailImagePath,
                        'JPEG',
                        "100"
                    );
                } elseif (strpos($file->getPath(), '.svg') !== false) {
                    $newCardThumbnailName = 'thumbnail_' . $newVersion . '.svg';
                    $newPath = $this->certificatePath . $newCardThumbnailName;

                    $this->web_fs->copy($file->getPath(), $newPath);

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
                true,
                $backgroundImagePath,
                $cardThumbnailImagePath
            );

            $this->templateRepository->save($template);

            return true;
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Error during certificate import: %s', $e->getMessage()));
            $this->logger->error($e->getTraceAsString());

            return false;
        } finally {
            $clean_up_import_dir();
        }
    }

    /**
     * @throws IOException
     */
    private function createArchiveDirectory(string $installationId): string
    {
        $dir = $this->buildArchivePath($installationId);

        if ($this->web_fs->hasDir($dir)) {
            $this->web_fs->deleteDir($dir);
        }
        $this->web_fs->createDir($dir);

        return $dir;
    }

    /**
     * @throws IOException
     */
    private function createTemporaryArchiveDirectory(string $installationId): string
    {
        $dir = $this->buildArchivePath($installationId);

        if ($this->tmp_fs->hasDir($dir)) {
            $this->tmp_fs->deleteDir($dir);
        }
        $this->tmp_fs->createDir($dir);

        return $dir;
    }

    private function buildArchivePath(string $installationId): string
    {
        $seperator = '__';
        $type = $this->objectHelper->lookupType($this->objectId);

        return implode($seperator, [
            $this->certificatePath . time(),
            $installationId,
            $type,
            $this->objectId,
            'certificate/'
        ]);
    }

    private function getBackgroundImageThumbnailPath(): string
    {
        return $this->certificatePath . 'background.jpg.thumb.jpg';
    }
}
