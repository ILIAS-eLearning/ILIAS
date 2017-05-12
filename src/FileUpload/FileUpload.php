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
 * @since 5.3
 * @version 1.0
 */
interface FileUpload {

	/**
	 * Moves all uploaded files to the given destination after the processors had processed the files.
	 * Only files which got accepted by the processors are moved. Please make sure to process the
	 * uploaded files first with the help of the process method.
	 *
	 * @param string $destination  The destination of the uploaded files.
	 *
	 * @return void
	 *
	 * @throws IllegalStateException Thrown if the files are not processed before invoking the moveFilesTo method.
	 */
	public function moveFilesTo($destination);


	/**
	 * Returns the current upload size limit in bytes.
	 *
	 * @return int
	 */
	public function uploadSizeLimit();


	/**
	 * Register a new preprocessor instance.
	 * The preprocessor instances are invoked for each uploaded file.
	 *
	 * @param PreProcessor $preProcessor    The preprocessor instance which should be registered.
	 *
	 * @return void
	 */
	public function register(PreProcessor $preProcessor);


	/**
	 * Invokes all preprocessors for each uploaded file
	 * in the sequence they got registered. If a processor fails or returns unexpected results
	 * the file which got processed is automatically rejected to prevent ILIAS from using unprocessed files.
	 *
	 * @return void
	 */
	public function process();


	/**
	 * Returns the results of the processing and moving operation of the uploaded files.
	 *
	 * @return UploadResult[]
	 * @throws IllegalStateException If the method is called before the files are processed.
	 */
	public function getResults();
}