<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Style\Content;

/**
 * Wrapper interface for session
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface Session
{
    public function set(string $key, string $value) : void;

    public function get(string $key) : string;

    public function clear(string $key) : void;
}
