<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use \ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes a close button.
 *
 * This does not implement the Button interface as there seem to be not many
 * commonalities between the standard/primary buttons and the close button.
 */
interface Close extends \ILIAS\UI\Component\Component, JavaScriptBindable
{
}
