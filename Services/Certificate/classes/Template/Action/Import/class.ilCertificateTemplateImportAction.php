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
    private readonly ilCertificateTemplateRepository $templateRepository;
    private readonly ilCertificateObjectHelper $objectHelper;
    private readonly ilCertificateUtilHelper $utilHelper;
    private readonly ilCertificateBackgroundImageFileService $fileService;
    private readonly \ILIAS\Data\Factory $df;
    private readonly SVGBlacklistPreProcessor $svg_blacklist_processor;

    public function __construct(
        private readonly int $objectId,
        private readonly string $certificatePath,
        private readonly ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        private readonly ilLogger $logger,
        private readonly Filesystem $web_fs,
        private readonly Filesystem $tmp_fs,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilDBInterface $database = null,
        ?ilCertificateBackgroundImageFileService $fileService = null,
        ?\ILIAS\Data\Factory $df = null,
        ?SVGBlacklistPreProcessor $svg_blacklist_processor = null
    ) {
        if ($database === null) {
            global $DIC;
            $database = $DIC->database();
        }

        if ($templateRepository === null) {
            $templateRepository = new ilCertificateTemplateDatabaseRepository($database, $logger);
        }
        $this->templateRepository = $templateRepository;

        if ($objectHelper === null) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if ($utilHelper === null) {
            $utilHelper = new ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;

        if ($fileService === null) {
            $fileService = new ilCertificateBackgroundImageFileService(
                $certificatePath,
                $web_fs
            );
        }
        $this->fileService = $fileService;
        $this->df = $df ?? new \ILIAS\Data\Factory();
        $this->svg_blacklist_processor = $svg_blacklist_processor ?? new SVGBlacklistPreProcessor();
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     */
    public function import(
        string $path_to_zip_file,
        string $filename,
        string $web_directory = CLIENT_WEB_DIR,
        string $storage_directory = CLIENT_DATA_DIR,
        string $ilias_version = ILIAS_VERSION_NUMERIC,
        string $installation_id = IL_INST_ID
    ): bool {
        $rel_tmp_import_path = $this->createTemporaryArchiveDirectory($installation_id);
        $abs_tmp_directory = rtrim($storage_directory, '/') . '/temp/';
        $rel_import_path = $this->createArchiveDirectory($installation_id);

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

        $result = $this->df->ok(true);

        try {
            return $result
                ->then(
                    function () use (
                        $path_to_zip_file,
                        $filename,
                        $abs_tmp_directory,
                        $rel_tmp_import_path
                    ): \ILIAS\Data\Result {
                        $abs_zip_path = $abs_tmp_directory . $rel_tmp_import_path . $filename;
                        $result = $this->utilHelper->moveUploadedFile(
                            $path_to_zip_file,
                            $filename,
                            $abs_zip_path
                        );
                        if ($result) {
                            return $this->df->ok($abs_zip_path);
                        }

                        return $this->df->error(
                            sprintf(
                                'Could not move uploaded file %s to %s',
                                $path_to_zip_file,
                                $abs_zip_path
                            )
                        );
                    }
                )
                ->then(
                    function (string $abs_zip_path) use (
                        $rel_tmp_import_path,
                        $abs_tmp_directory
                    ): \ILIAS\Data\Result {
                        $abs_unzip_destination_dir = $abs_tmp_directory . $rel_tmp_import_path;
                        $unzip = $this->utilHelper->unzip(
                            $abs_zip_path,
                            $abs_unzip_destination_dir,
                            true
                        );

                        $unzipped = $unzip->extract();

                        // Cleanup memory, otherwise there will be issues with NFS-based file systems after `listContents` has been called
                        unset($unzip);

                        if ($unzipped) {
                            $this->utilHelper->renameExecutables($abs_unzip_destination_dir);

                            return $this->df->ok($unzipped);
                        }

                        return $this->df->error(
                            sprintf(
                                'Could not unzip file %s to %s',
                                $abs_zip_path,
                                $abs_unzip_destination_dir
                            )
                        );
                    }
                )
                ->then(function () use ($rel_import_path, $rel_tmp_import_path, $filename): \ILIAS\Data\Result {
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

                    $num_xml_files = 0;
                    $num_background_images = 0;
                    $num_tile_images = 0;
                    $contents = $this->web_fs->listContents($rel_import_path);
                    foreach ($contents as $file) {
                        if (!$file->isFile()) {
                            continue;
                        }

                        if (str_contains($file->getPath(), '.xml')) {
                            ++$num_xml_files;
                        }

                        if (str_contains($file->getPath(), '.svg')) {
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
                                return $this->df->error(
                                    sprintf('SVG file check failed. Reason: %s', $processing_result->getMessage())
                                );
                            }
                        }

                        if (str_contains($file->getPath(), '.jpg')) {
                            ++$num_background_images;
                        }
                    }

                    if ($num_xml_files !== 1) {
                        return $this->df->error(sprintf(
                            'The number of XML files found in import directory does not match the expectations (expected: 1, given: %s): %s',
                            $num_xml_files,
                            $rel_tmp_import_path
                        ));
                    }
                    if ($num_background_images > 1) {
                        return $this->df->error(sprintf(
                            'The number of background files found in import directory does not match the expectations (expected: 0-1, given: %d): %s',
                            $num_background_images,
                            $rel_tmp_import_path
                        ));
                    }
                    if ($num_tile_images > 1) {
                        return $this->df->error(sprintf(
                            'The number of tile image files found in import directory does not match the expectations (expected: 0-1, given: %d): %s',
                            $num_tile_images,
                            $rel_tmp_import_path
                        ));
                    }

                    return $this->df->ok($contents);
                })
                /**
                 * @var list<ILIAS\Filesystem\DTO\Metadata> $contents
                 */
                ->then(function (array $contents) use ($web_directory, $ilias_version) {
                    $certificate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

                    $current_version = $certificate->getVersion();
                    $upcoming_version = $current_version + 1;
                    $background_image_path = $certificate->getBackgroundImagePath();
                    $cart_thumbnail_image_path = $certificate->getThumbnailImagePath();
                    $xsl = $certificate->getCertificateContent();

                    foreach ($contents as $file) {
                        if (!$file->isFile()) {
                            continue;
                        }

                        if (str_contains($file->getPath(), '.xml')) {
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

                                    if ($fileName === '[BACKGROUND_IMAGE]') {
                                        $basePath = '';
                                    } elseif ($basePath !== '') {
                                        $basePath .= '/';
                                    }

                                    return 'url(' . $basePath . $fileName . ')';
                                },
                                $xsl
                            );
                        } elseif (str_contains($file->getPath(), '.jpg')) {
                            $new_background_image_name = 'background_' . $upcoming_version . '.jpg';
                            $new_path = $this->certificatePath . $new_background_image_name;
                            $this->web_fs->copy($file->getPath(), $new_path);

                            $background_image_path = $this->certificatePath . $new_background_image_name;
                            // upload of the background image, create a thumbnail

                            $background_image_thumbnail_path = $this->getBackgroundImageThumbnailPath();

                            $thumbnail_image_path = $web_directory . $background_image_thumbnail_path;

                            $original_image_path = $web_directory . $new_path;
                            $this->utilHelper->convertImage(
                                $original_image_path,
                                $thumbnail_image_path,
                                '100'
                            );
                        } elseif (str_contains($file->getPath(), '.svg')) {
                            $new_card_thumbnail_name = 'thumbnail_' . $upcoming_version . '.svg';
                            $new_path = $this->certificatePath . $new_card_thumbnail_name;

                            $this->web_fs->copy($file->getPath(), $new_path);

                            $cart_thumbnail_image_path = $this->certificatePath . $new_card_thumbnail_name;
                        }
                    }

                    $serialized_template_values = json_encode(
                        $this->placeholderDescriptionObject->getPlaceholderDescriptions(),
                        JSON_THROW_ON_ERROR
                    );

                    $upcoming_version_hash = hash(
                        'sha256',
                        implode('', [
                            $xsl,
                            $background_image_path,
                            $serialized_template_values,
                            $cart_thumbnail_image_path
                        ])
                    );

                    $template = new ilCertificateTemplate(
                        $this->objectId,
                        $this->objectHelper->lookupType($this->objectId),
                        $xsl,
                        $upcoming_version_hash,
                        $serialized_template_values,
                        $upcoming_version,
                        $ilias_version,
                        time(),
                        false,
                        $background_image_path,
                        $cart_thumbnail_image_path
                    );

                    $this->templateRepository->save($template);

                    return null;
                })
                ->isOK();
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
