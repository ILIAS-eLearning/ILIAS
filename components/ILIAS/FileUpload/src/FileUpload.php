<?php

namespace ILIAS\FileUpload;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\FileUpload\Processor\PreProcessor;

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
 * Class FileUpload
 *
 * This interface provides the public operations for the
 * rest of ILIAS.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
interface FileUpload
{
    /**
     * Moves all uploaded files to the given destination after the processors had processed the
     * files. Only files which got accepted by the processors are moved. Please make sure to
     * process the uploaded files first with the help of the process method.
     *
     * Please note that the Location interface defines all valid location.
     *
     * @param string $destination The destination of the uploaded files.
     * @param int    $location    The filesystem location which should be used.
     *
     *
     * @throws IllegalStateException        Thrown if the files are not processed before invoking the moveFilesTo method.
     * @throws \InvalidArgumentException    Thrown if the location is invalid.
     * @since 5.3
     * @see   Location
     */
    public function moveFilesTo(string $destination, int $location = Location::STORAGE): void;


    /**
     * Moves a single File (the attributes, metadata and upload-status of which are contained in UploadResult)
     * to the given destination. The destination is a relative path which refers to the path of the location.
     *
     * @param UploadResult $uploadResult Which upload result do you want to move?
     * @param string       $destination  Where do you want to move the file?
     * @param int          $location     Location::[STORAGE|WEB|CUSTOMIZING]
     * @param string       $file_name    Do you want to rename the file?
     * @param bool         $override_existing Override existing file with same name
     */
    public function moveOneFileTo(
        UploadResult $uploadResult,
        string $destination,
        int $location = Location::STORAGE,
        string $file_name = '',
        bool $override_existing = false
    ): bool;


    /**
     * Returns the current upload size limit in bytes.
     *
     * @since 5.3
     */
    public function uploadSizeLimit(): int;


    /**
     * Register a new preprocessor instance.
     * The preprocessor instances are invoked for each uploaded file.
     *
     * @param PreProcessor $preProcessor The preprocessor instance which should be registered.
     *
     *
     * @throws IllegalStateException If the register method is called after the files already got
     *                               processed.
     * @since 5.3
     */
    public function register(PreProcessor $preProcessor): void;


    /**
     * Invokes all preprocessors for each uploaded file
     * in the sequence they got registered. If a processor fails or returns unexpected results
     * the file which got processed is automatically rejected to prevent ILIAS from using
     * unprocessed files.
     *
     *
     * @throws IllegalStateException If the files already got processed.
     * @since 5.3
     */
    public function process(): void;


    /**
     * Returns the results of the processing and moving operation of the uploaded files.
     *
     * @return UploadResult[]
     *
     * @throws IllegalStateException If the method is called before the files are processed.
     * @since 5.3
     */
    public function getResults(): array;


    /**
     * Return (bool)true if one ore more file-uploads are in the current request, (bool)false if not
     *
     *
     * @since 5.3
     */
    public function hasUploads(): bool;

    /**
     * Return (bool)true if the current upload has already been processed
     *
     *
     * @since 5.3
     */
    public function hasBeenProcessed(): bool;
}
