<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/**
 * Class FilenameOverridePreProcessor
 *
 * PreProcessor which overrides the filename with a given one
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class FilenameOverridePreProcessor implements PreProcessor {

	/**
	 * @var string
	 */
	private $filename;


	/**
	 * BlacklistExtensionPreProcessor constructor.
	 *
	 * @param string $filename
	 */
	public function __construct($filename) { $this->filename = $filename; }


	/**
	 * @inheritDoc
	 */
	public function process(FileStream $stream, Metadata $metadata) {
		$metadata->setFilename($this->filename);

		return new ProcessingStatus(ProcessingStatus::OK, 'Filename changed');
	}
}