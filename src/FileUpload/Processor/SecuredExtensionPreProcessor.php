<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

/**
 * Class SecuredExtensionPreProcessor
 *
 * PreProcessor which allows only whitelisted file extensions.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class SecuredExtensionPreProcessor implements PreProcessor {

	/**
	 * @var string[]
	 */
	private $whitelist;


	/**
	 * SecuredExtensionPreProcessor constructor.
	 *
	 * Example:
	 * ['jpg', 'svg', 'png']
	 *
	 * Matches:
	 * example.jpg
	 * example.svg
	 * example.png
	 *
	 * No Match:
	 * example.apng
	 * example.png.exe
	 * ...
	 *
	 * @param \string[] $whitelist The file extensions which should be whitelisted.
	 *                             Other filenames will be renamed tithout dots and
	 *                             with a .sec suffix
	 */
	public function __construct(array $whitelist) { $this->whitelist = $whitelist; }


	/**
	 * @inheritDoc
	 */
	public function process(FileStream $stream, Metadata $metadata) {
		if ($this->isWhitelisted($metadata->getFilename())) {
			return new ProcessingStatus(ProcessingStatus::OK, 'Extension complies with whitelist.');
		}

		$filename = $metadata->getFilename();
		$pi = pathinfo($filename);
		// if extension is not in white list, remove all "." and add ".sec" extension
		$basename = str_replace(".", "", $pi["basename"]);
		if (trim($basename) == "") {
			return new ProcessingStatus(ProcessingStatus::REJECTED, 'Invalid upload filename.');
		}
		$basename .= ".sec";
		if ($pi["dirname"] != "" && ($pi["dirname"] != "." || substr($filename, 0, 2) == "./")) {
			$filename = $pi["dirname"] . "/" . $basename;
		} else {
			$filename = $basename;
		}

		$metadata->setFilename($filename);

		return new ProcessingStatus(ProcessingStatus::OK, 'Extension has been secured with .sec.');
	}


	private function isWhitelisted($filename) {
		$extensions = explode('.', $filename);
		$extension = null;

		if (count($extensions) <= 1) {
			$extension = '';
		} else {
			$extension = end($extensions);
		}

		return in_array(strtolower($extension), $this->whitelist);
	}
}