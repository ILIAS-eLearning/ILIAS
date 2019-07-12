<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * Signals that some goal won't be achievable by actions of the system ever.
 *
 * e.g.:
 *   - some PHP-package is missing
 *   - some binary is missing
 *   - some permissions are missing
 */
class UnachievableException extends \LogicException {
}
