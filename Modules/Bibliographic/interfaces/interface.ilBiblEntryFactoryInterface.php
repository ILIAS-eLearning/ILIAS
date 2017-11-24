<?php

/**
 * Interface ilBiblEntryFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblEntryFactoryInterface {

	/**
	 * @param int    $id
	 * @param string $type_string
	 *
	 * @deprecated This has to be refactored to type_id and not type_string
	 *
	 * @return \ilBiblEntryInterface
	 */
	public function findByIdAndTypeString($id, $type_string);
}
