<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Export;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Export\ExportFormat;

/**
 * Class AbstractExportFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Export
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractExportFormat implements ExportFormat {

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
}
