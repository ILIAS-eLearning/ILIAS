<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Tool\Factory\isToolItem;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Slate;

/**
 * Class
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait SlateSessionStateCode
{

    use Hasher;


    /**
     * @param Slate $slate
     *
     * @return Slate
     */
    public function addOnloadCode(Slate $slate, isItem $item) : Slate
    {
        if ($item instanceof isToolItem) {
            return $slate->withAdditionalOnLoadCode(static function (string $id) : string {
                return "il.UI.maincontrols.mainbar.addToolEntry('$id');";
            });
        }

        return $slate;
    }
}
