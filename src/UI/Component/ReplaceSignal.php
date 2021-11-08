<?php declare(strict_types=1);

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
     */
    public function withAsyncRenderUrl(string $url) : ReplaceSignal;

    /**
     * Get the url called to return the content.
     */
    public function getAsyncRenderUrl() : string;
}
