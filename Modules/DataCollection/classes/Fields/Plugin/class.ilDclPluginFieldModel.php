<?php

/**
 * Class ilDclPluginFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 */
class ilDclPluginFieldModel extends ilDclBaseFieldModel
{

	/**
	 * @inheritDoc
	 */
	public function getValidFieldProperties() {
		return array(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME);
	}
}