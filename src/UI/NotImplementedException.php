<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

use Exception;

/**
 * This exception indicates that an UI component was accepted by the JF but is
 * not backed by a real implementation.
 */
class NotImplementedException extends Exception
{
}
