<?php

use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Services;

/**
 * Class ilGlobalPageTemplate
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGlobalPageTemplate extends ilGlobalTemplate implements ilGlobalTemplateInterface {

	/**
	 * @var Services
	 */
	private $gs;
	/**
	 * @var UIServices
	 */
	private $ui;


	/**
	 * @inheritDoc
	 */
	public function __construct(Services $gs, UIServices $ui, string $file, bool $flag1, bool $flag2, bool $in_module = false, $vars = "DEFAULT", bool $plugin = false, bool $a_use_cache = true) {
		parent::__construct($file, $flag1, $flag2, $in_module, $vars, $plugin, $a_use_cache);
		$this->ui = $ui;
		$this->gs = $gs;
	}


	/**
	 * @inheritDoc
	 */
	public function printToStdout($part = "DEFAULT", $a_fill_tabs = true, $a_skip_main_menu = false) {
		// parent::printToStdout($part, $a_fill_tabs, $a_skip_main_menu);

		$metabar = $this->ui->factory()->mainControls()->metaBar();
		$mainbar = $this->ui->factory()->mainControls()->mainBar();
		$page = $this->ui->factory()->layout()->page()->standard($metabar, $mainbar, [$this->ui->factory()->legacy("CONTENT")]);

		print $this->ui->renderer()->render([$page]);
	}
}
