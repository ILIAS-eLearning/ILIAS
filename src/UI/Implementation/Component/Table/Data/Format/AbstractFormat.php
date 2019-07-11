<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use GuzzleHttp\Psr7\Stream;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
use ilMimeTypeUtil;

/**
 * Class AbstractFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
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
	public function getDisplayTitle(): string {
		return $this->dic->language()->txt(Table::LANG_MODULE . "_format_" . $this->getFormatId());
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
