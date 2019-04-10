<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Wraps global ilTemplate to provide JavaScriptBinding. 
 */
class ilJavaScriptBinding implements JavaScriptBinding {
	const PREFIX = "il_ui_fw_";

	/**
	 * @var \ilGlobalTemplate
	 */
	private	$global_tpl;

	/**
	 * Cache for all registered JS code
	 *
	 * @var array
	 */
	protected $code = array();

	/**
	 * ilJavaScriptBinding constructor.
	 * ilGlobalTemplate type hint is removed here, since currently (Apr 2019)
	 * other global templates (e.g. ilPortfolioGlobalTemplate) are being passed
	 * (see ilInitialisation initHTML()).
	 * @param $il_template
	 */
	public function __construct($global_tpl) {
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
		$this->code[] = $code;
	}

	/**
	 * @inheritdoc
	 */
	public function getOnLoadCodeAsync() {
		if (!count($this->code)) {
			return '';
		}
        $js_out = '<script data-replace-marker="script">' . implode("\n", $this->code) . '</script>';
        $this->code = [];
		return $js_out;
	}

}
