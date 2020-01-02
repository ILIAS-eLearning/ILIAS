<?php

namespace ILIAS\FileUpload;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\FileUpload\Processor\PreProcessor;

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
     *
     * @param int    $location    The filesystem location which should be used.
     *
     * @return void
     *
     * @throws IllegalStateException        Thrown if the files are not processed before invoking
     *                                      the moveFilesTo method.
     * @throws \InvalidArgumentException    Thrown if the location is invalid.
     * @since 5.3
     *
     * @see   Location
     */
    public function moveFilesTo($destination, $location = Location::STORAGE);


    /**
     * Moves a single File (the attributes, metadata and upload-status of which are contained in UploadResult)
     * to the given destination. The destination is a relative path which refers to the path of the location.
     *
     * @param UploadResult $UploadResult Which upload result do you want to move?
     * @param string       $destination  Where do you want to move the file?
     * @param int          $location     Location::[STORAGE|WEB|CUSTOMIZING]
     * @param string       $file_name    Do you want to rename the file?
     * @param bool         $override_existing Override existing file with same name
     *
     * @return void
     */
    public function moveOneFileTo(UploadResult $UploadResult, $destination, $location = Location::STORAGE, $file_name = '', $override_existing = false);


    /**
     * Returns the current upload size limit in bytes.
     *
     * @return int
     * @since 5.3
     */
    public function uploadSizeLimit();


    /**
     * Register a new preprocessor instance.
     * The preprocessor instances are invoked for each uploaded file.
     *
     * @param PreProcessor $preProcessor The preprocessor instance which should be registered.
     *
     * @return void
     *
     * @throws IllegalStateException If the register method is called after the files already got
     *                               processed.
     * @since 5.3
     */
    public function register(PreProcessor $preProcessor);


    /**
     * Invokes all preprocessors for each uploaded file
     * in the sequence they got registered. If a processor fails or returns unexpected results
     * the file which got processed is automatically rejected to prevent ILIAS from using
     * unprocessed files.
     *
     * @return void
     *
     * @throws IllegalStateException If the files already got processed.
     * @since 5.3
     */
    public function process();


    /**
     * Returns the results of the processing and moving operation of the uploaded files.
     *
     * @return UploadResult[]
     *
     * @throws IllegalStateException If the method is called before the files are processed.
     * @since 5.3
     */
    public function getResults();


    /**
     * Return (bool)true if one ore more file-uploads are in the current request, (bool)false if not
     *
     * @return bool
     *
     * @since 5.3
     */
    public function hasUploads();

    /**
     * Return (bool)true if the current upload has already been processed
     *
     * @return bool
     *
     * @since 5.3
     */
    public function hasBeenProcessed();
}
