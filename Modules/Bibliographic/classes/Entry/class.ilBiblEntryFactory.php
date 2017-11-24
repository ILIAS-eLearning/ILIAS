<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBiblEntryFactory
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblEntryFactory implements ilBiblEntryFactoryInterface {

	/**
	 * @inheritDoc
	 */
	public function findByIdAndTypeString($id, $type_string) {
		return ilBiblEntry::getInstance($type_string, $id);
	}
}
