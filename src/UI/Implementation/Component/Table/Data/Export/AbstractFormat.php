<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Export;

use GuzzleHttp\Psr7\Stream;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ilMimeTypeUtil;

/**
 * Class AbstractFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Export
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFormat implements Format {

	/**
	 * @var Container
	 */
	protected $dic;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		$this->dic = $dic;
	}


	/**
	 * @inheritDoc
	 */
	public function devliver(string $data, string $title, string $table_id): void {
		$filename = $title . "." . $this->getFileExtension();

		$stream = new Stream(fopen("php://memory", "rw"));
		$stream->write($data);

		$this->dic->http()->saveResponse($this->dic->http()->response()->withBody($stream)->withHeader("Content-Disposition", 'attachment; filename="'
			. $filename . '"')// Filename
		->withHeader("Content-Type", ilMimeTypeUtil::APPLICATION__OCTET_STREAM)// Force download
		->withHeader("Expires", "0")->withHeader("Pragma", "public"));// No cache

		$this->dic->http()->sendResponse();

		exit;
	}
}
