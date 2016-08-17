<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Wraps global ilTemplate to provide JavaScriptBinding. 
 */
class ilJavaScriptBinding implements JavaScriptBinding {
	/**
	 * @var \ilTemplate
	 */
	private	$global_tpl;

	/**
	 * @var	integer
	 */
	private $id_count = 0;

	public function __construct(\ilTemplate $global_tpl) {
		$this->global_tpl = $global_tpl;
	}

	/**
	 * @inheritdoc
	 */
	public function createId() {
		$this->id_count++;
		return "id_".$this->id_count;
	}

	/**
	 * @inheritdoc
	 */
	public function addOnLoadCode($code) {
		$this->global_tpl->addOnLoadCode($code);
	}
}
