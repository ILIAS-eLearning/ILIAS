<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * Class
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait SlateSessionStateCode
{
    use Hasher;
    
    /**
     * @param Slate $slate
     * @return Slate
     */
    public function addOnloadCode(Slate $slate, isItem $item) : Slate
    {
        return $slate;
    }
}
