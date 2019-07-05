<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * The ILIAS-setup-console-application.
 *
 * TODO: Add some metainformation to the app, such as name.
 */
class App extends Application {
	const NAME = "The ILIAS Setup";

	public function __construct(Command ...$commands) {
		parent::__construct(self::NAME);
		foreach ($commands as $c) {
			$this->add($c);	
		}
	}
}
