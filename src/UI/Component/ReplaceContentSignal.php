<?php declare(strict_types=1);

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
     */
    public function withAsyncRenderUrl(string $url) : ReplaceContentSignal;

    /**
     * Get the url called to return the content.
     */
    public function getAsyncRenderUrl() : string;
}
