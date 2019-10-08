<?php

namespace ILIAS\UI\Component;

/**
 * This signal replaces a component by ajax
 *
 * @author Alex Killing <killing@leifos.com>
 */
interface ReplaceSignal extends Signal
{

    /**
     * Get the same signal returning an element from the given url
     *
     * @param string $url
     *
     * @return ReplaceSignal
     */
    public function withAsyncRenderUrl($url);

    /**
     * Get the url called to return the content.
     *
     * @return string
     */
    public function getAsyncRenderUrl();
}
