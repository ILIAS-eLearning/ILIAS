<?php

namespace ILIAS\UI\Component;

/**
 * This signal replaces the content of a component by ajax
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Jesús Lópéz <lopez@leifos.com>
 * @author Alex Killing <killing@leifos.com>
 */
interface ReplaceContentSignal extends Signal
{

    /**
     * Get the same signal returning an element from the given url
     *
     * @param string $url
     *
     * @return ReplaceContentSignal
     */
    public function withAsyncRenderUrl($url);

    /**
     * Get the url called to return the content.
     *
     * @return string
     */
    public function getAsyncRenderUrl();
}
