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
use ILIAS\FileUpload\Processor\SVGBlacklistPreProcessor;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
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
    private readonly \ILIAS\Data\Factory $df;
    private readonly SVGBlacklistPreProcessor $svg_blacklist_processor;

    public function __construct(
        private readonly int $objectId,
        private readonly string $certificatePath,
        private readonly ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        private readonly ilLogger $logger,
        private readonly Filesystem $web_fs,
        private readonly Filesystem $tmp_fs,
        private readonly IRSS $irss,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilDBInterface $database = null,
        ?\ILIAS\Data\Factory $df = null,
        ?SVGBlacklistPreProcessor $svg_blacklist_processor = null
    ) {
        global $DIC;
        if ($database === null) {
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
        $this->stakeholder = new ilCertificateTemplateStakeholder();
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

        $clean_up_import_dir = function () use (&$rel_tmp_import_path): void {
            try {
                if ($this->tmp_fs->hasDir($rel_tmp_import_path)) {
                    $this->tmp_fs->deleteDir($rel_tmp_import_path);
                }
            } catch (Throwable $e) {
                $this->logger->error(sprintf("Can't clean up temporary import directory: %s", $e->getMessage()));
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
                ->then(function () use ($rel_tmp_import_path, $filename): \ILIAS\Data\Result {
                    if ($this->tmp_fs->has($rel_tmp_import_path . $filename)) {
                        $this->tmp_fs->delete($rel_tmp_import_path . $filename);
                    }

                    $num_xml_files = 0;
                    $num_background_images = 0;
                    $num_tile_images = 0;
                    $contents = $this->tmp_fs->listContents($rel_tmp_import_path, true);
                    foreach ($contents as $file) {
                        if (!$file->isFile()) {
                            continue;
                        }

                        if (str_contains($file->getPath(), '.xml')) {
                            ++$num_xml_files;
                        }

                        if (str_contains($file->getPath(), '.svg')) {
                            $stream = $this->tmp_fs->readStream($file->getPath());
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
                ->then(function (array $contents) use ($ilias_version) {
                    $certificate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

                    $current_version = $certificate->getVersion();
                    $upcoming_verion = $current_version + 1;
                    $xsl = $certificate->getCertificateContent();

                    foreach ($contents as $file) {
                        if (!$file->isFile()) {
                            continue;
                        }

                        if (str_contains($file->getPath(), '.xml')) {
                            $xsl = $this->tmp_fs->read($file->getPath());
                            $xsl = preg_replace(
                                "/url\([']{0,1}(.*?)[']{0,1}\)/",
                                'url([BACKGROUND_IMAGE])',
                                $xsl
                            );
                        } elseif (str_contains($file->getPath(), '.jpg')) {
                            $background_rid = $this->irss->manage()->stream(
                                $this->tmp_fs->readStream($file->getPath()),
                                $this->stakeholder
                            );
                        } elseif (str_contains($file->getPath(), '.svg')) {
                            $tile_image_rid = $this->irss->manage()->stream(
                                $this->tmp_fs->readStream($file->getPath()),
                                $this->stakeholder
                            );
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
                            isset($background_rid) ? $this->irss->manage()->getResource(
                                $background_rid
                            )->getStorageID() : '',
                            $serialized_template_values,
                            isset($tile_image_rid) ? $this->irss->manage()->getResource(
                                $tile_image_rid
                            )->getStorageID() : ''
                        ])
                    );

                    $template = new ilCertificateTemplate(
                        $this->objectId,
                        $this->objectHelper->lookupType($this->objectId),
                        $xsl,
                        $upcoming_version_hash,
                        $serialized_template_values,
                        $upcoming_verion,
                        $ilias_version,
                        time(),
                        false,
                        '',
                        '',
                        isset($background_rid) ? $background_rid->serialize() : '',
                        isset($tile_image_rid) ? $tile_image_rid->serialize() : ''
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
}
