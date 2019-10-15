<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * Prompts a user to confirm or deny a certain message.
 */
interface ConfirmationRequester {
	public function confirmOrDeny(string $message) : bool;
}
