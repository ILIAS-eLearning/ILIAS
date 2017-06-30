<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Wraps global ilTemplate to provide JavaScriptBinding. 
 */
class ilJavaScriptBinding implements JavaScriptBinding {
	const PREFIX = "il_ui_fw_";

	/**
	 * @var \ilTemplate
	 */
	private	$global_tpl;

	public function __construct(\ilTemplate $global_tpl) {
		$this->global_tpl = $global_tpl;
	}

	/**
	 * @inheritdoc
	 */
	public function createId() {
		return str_replace(".", "_", uniqid(self::PREFIX, true));
	}

	/**
	 * @inheritdoc
	 */
	public function addOnLoadCode($code) {
		$this->global_tpl->addOnLoadCode($code);
	}
}
