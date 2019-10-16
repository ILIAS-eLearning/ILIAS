<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes a close button.
 *
 * This does not implement the Button interface as there seem to be not many
 * commonalities between the standard/primary buttons and the close button.
 */
interface Close extends \ILIAS\UI\Component\Component, JavaScriptBindable, Clickable
{

    /**
     * @param URI $uri when you need an URl to be called instead of the JS event
     *
     * @return Close
     */
    public function withAction(URI $uri) : Close;


    /**
     * @return URI
     */
    public function getAction() : ?URI;
}
