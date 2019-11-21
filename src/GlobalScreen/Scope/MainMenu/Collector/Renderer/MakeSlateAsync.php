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

        // $toggle_signal = $slate->getToggleSignal();

        $serialize = $item->getProviderIdentification()->serialize();
        $hash = $this->hash($serialize);
        $url = "./src/GlobalScreen/Client/content.php?item=" . $hash;

        $replace_signal = $slate->getReplaceSignal()->withAsyncRenderUrl($url);

        $slate = $slate->appendOnInView($replace_signal);

        return $slate;
    }
}
