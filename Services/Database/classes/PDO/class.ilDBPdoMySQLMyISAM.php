<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('class.ilDBPdoMySQL.php');

/**
 * Class ilDBPdoMySQLMyISAM
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoMySQLMyISAM extends ilDBPdoMySQL implements ilDBInterface {
	/**
	 * @return bool
	 */
	public function supportsFulltext() {
		return true;
	}
}

