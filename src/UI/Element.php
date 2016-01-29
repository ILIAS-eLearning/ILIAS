<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * Interface to a general element in the UI.
 */
interface Element {
    /**
     * Render element to an HTML string.
     *
     * @return  string
     */
    public function to_html_string();
}