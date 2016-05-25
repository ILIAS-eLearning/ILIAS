<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('class.ilDBPdo.php');

/**
 * Class ilDBPdoMySQL
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilDBPdoMySQL extends ilDBPdo implements ilDBInterface {

	/**
	 * @return bool
	 */
	public function supportsTransactions() {
		return true;
	}
}

