<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * The interface a class has to fullfill if it should be used as leaf in a
 * program.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */

interface ilTrainingProgrammeLeaf {
	/**
	 * Get the ILIAS object id of the leaf.
	 *
	 * @return int
	 */
	public function getId();

	/**
	 * Get the ILIAS reference id of the leaf.
	 *
	 * @return int | null
	 */
	public function getRefId();
}

?>