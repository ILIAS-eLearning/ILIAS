<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/**
 * Class BlacklistExtensionPreProcessor
 *
 * PreProcessor which denies all blacklisted file extensions.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class BlacklistExtensionPreProcessor implements PreProcessor {

	/**
	 * @var string
	 */
	private $reason;
	/**
	 * @var string[]
	 */
	private $blacklist;


	/**
	 * BlacklistExtensionPreProcessor constructor.
	 *
	 * Example:
	 * ['jpg', 'svg', 'png', '']
	 *
	 * Matches:
	 * example.jpg
	 * example.svg
	 * example.png
	 * example
	 *
	 * No Match:
	 * example.apng
	 * example.png.exe
	 * ...
	 *
	 * @param \string[] $blacklist The file extensions which should be blacklisted.
	 * @param string    $reason
	 */
	public function __construct(array $blacklist, $reason = 'Extension is blacklisted.') {
		$this->blacklist = $blacklist;
		$this->reason = $reason;
	}


	/**
	 * @inheritDoc
	 */
	public function process(FileStream $stream, Metadata $metadata) {
		if ($this->isBlacklisted($metadata, $stream)) {
			return new ProcessingStatus(ProcessingStatus::REJECTED, $this->reason);
		}

		return new ProcessingStatus(ProcessingStatus::OK, 'Extension is not blacklisted.');
	}


	/**
	 * Checks if the current filename has a listed extension. (*.png, *.mp4 etc ...)
	 *
	 * @param Metadata   $metadata
	 *
	 * @param FileStream $stream
	 *
	 * @return bool True if the extension is listed, otherwise false.
	 */
	private function isBlacklisted(Metadata $metadata, FileStream $stream) {
		$filename = $metadata->getFilename();
		$extension = $this->getExtensionForFilename($filename);

		if (strtolower($extension) === 'zip') {
			$zip_file_path = $stream->getMetadata('uri');
			$zip = new \ZipArchive();
			$zip->open($zip_file_path);

			for ($i = 0; $i < $zip->numFiles; $i++) {
				$original_path = $zip->getNameIndex($i);
				if (in_array($this->getExtensionForFilename($original_path), $this->blacklist)) {
					$zip->close();

					return true;
				}
			}
			$zip->close();
		}

		return in_array($extension, $this->blacklist);
	}


	/**
	 * @param $filename
	 *
	 * @return null|string
	 */
	private function getExtensionForFilename($filename) {
		$extensions = explode('.', $filename);
		$extension = null;

		if (count($extensions) <= 1) {
			$extension = '';
		} else {
			$extension = strtolower(end($extensions));
		}

		return $extension;
	}
}