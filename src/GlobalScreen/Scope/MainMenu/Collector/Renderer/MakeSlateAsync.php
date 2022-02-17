<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * Trait MakeSlateAsync
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait MakeSlateAsync
{
    use Hasher;


    /**
     * @param Slate                       $slate
     * @param supportsAsynchronousLoading $item
     *
     * @return Slate
     */
    protected function addAsyncLoadingCode(Slate $slate, supportsAsynchronousLoading $item) : Slate
    {
        if ($item->supportsAsynchronousLoading() === false) {
            return $slate;
        }

        $serialize = $item->getProviderIdentification()->serialize();
        $replace_signal = $slate->getReplaceSignal()->withAsyncRenderUrl("./gs_content.php?item={$this->hash($serialize)}");

        $slate = $slate->appendOnFirstView($replace_signal);

        return $slate;
    }
}
