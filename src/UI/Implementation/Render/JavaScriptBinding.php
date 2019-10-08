<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Provides methods to interface with javascript.
 */
interface JavaScriptBinding
{
    /**
     * Create a fresh unique id.
     *
     * This MUST return a new id on every call.
     *
     * @return	string
     */
    public function createId();

    /**
     * Add some JavaScript-statements to the on-load handler of the page.
     *
     * @param	string	$code
     * @return	null
     */
    public function addOnLoadCode($code);

    /**
     * Get all the registered on-load javascript code for the async context, e.g. return all code
     * inside <script> tags
     *
     * @return string
     */
    public function getOnLoadCodeAsync();
}
