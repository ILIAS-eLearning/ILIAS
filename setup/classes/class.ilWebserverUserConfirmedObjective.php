<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilWebserverUserConfirmedObjective extends Setup\AdminConfirmedObjective {
	public function __construct() {
		parent::__construct(
			"Are you running this application with the same user that the webserver uses?\n".
			"If this is not the case there might be problems accessing files via the web\n".
			"interface later on."
		);
	}
}
