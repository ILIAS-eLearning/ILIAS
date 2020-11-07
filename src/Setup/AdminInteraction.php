<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * This defines ways in which objectives may interact with admins during the
 * setup.
 */
interface AdminInteraction
{
    public function startProgress(int $max) : void;
    public function advanceProgress() : void;
    public function stopProgress() : void;
    public function inform(string $message) : void;
    public function confirmOrDeny(string $message) : bool;
    public function confirmExplicit(string $message, string $type_text_to_confirm) : bool;
}
