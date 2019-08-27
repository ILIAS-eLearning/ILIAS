<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Client\ItemState;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
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
        $toggle_signal = $slate->getToggleSignal();
        $identification = $item->getProviderIdentification()->serialize();

        $item_state = new ItemState($item->getProviderIdentification());

        if ($item_state->isItemActive()) {
            $slate = $slate->withEngaged(true);
        }

        $level = $this->getLevel($item);

        $slate = $slate->withAdditionalOnLoadCode(
            function ($id) use ($toggle_signal, $identification, $level) {
                $identification = addslashes($identification);

                return "
                il.GS.Client.register(il.GS.Identification.getFromServerSideString('$identification'), '$id', $level);
                
                $(document).on('{$toggle_signal}', function(event, signalData) {
                    il.GS.Client.trigger('$id');
                    return false;
                });
                ";
            }
        );

        /** @var Slate $slate */
        return $slate;
    }


    /**
     * @param isItem $item
     *
     * @return int
     */
    private function getLevel(isItem $item) : int
    {
        switch (true) {
            case ($item instanceof isTopItem):
                $level = ItemState::LEVEL_OF_TOPITEM;
                break;
            case ($item instanceof Tool):
                $level = ItemState::LEVEL_OF_TOOL;
                break;
            default:
                $level = ItemState::LEVEL_OF_SUBITEM;
        }

        return $level;
    }
}
