<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Trigger\TriggerAction;

/**
 * Base class for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class Modal implements Component\Modal\Modal {

	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @inheritdoc
	 */
	public function getShowAction() {
		return new ShowAction($this);
	}


	/**
	 * @inheritdoc
	 */
	public function getCloseAction() {
		return new CloseAction($this);
	}


	/**
	 * @inheritdoc
	 */
	public function getShowAsyncAction($ajax_url) {
		return new ShowAsyncAction($this, $ajax_url);
	}
}
