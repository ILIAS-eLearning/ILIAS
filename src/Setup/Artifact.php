<?php

namespace ILIAS\Setup;

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * An artifact is some file that is build on demand per installation and is not
 * shipped with the ILIAS source.
 */
interface Artifact {
	/**
	 * This method will be called from the source, which wants to save the artifact.
	 */
	public function serialize() : string;
}
