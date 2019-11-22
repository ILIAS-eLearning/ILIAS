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
        return $slate;
        if ($item instanceof Tool) {
            $signal = $slate->getEngageSignal();
        } else {
            $signal = $slate->getToggleSignal();
        }

        $signal_generator = new \ILIAS\UI\Implementation\Component\SignalGenerator();
        $in_view_signal = $signal_generator->create();

        $slate = $slate->appendOnInView($in_view_signal);

        $identification = $item->getProviderIdentification()->serialize();
        $item_state = new ItemState($item->getProviderIdentification());

        // if ($item_state->isItemActive()) {
        //     $slate = $slate->withEngaged(true);
        // }

        $level = $this->getLevel($item);

        $slate = $slate->withAdditionalOnLoadCode(
            function ($id) use ($in_view_signal, $identification, $level) {
                $identification = addslashes($identification);

                return "
                il.GS.Client.register(il.GS.Identification.getFromServerSideString('{$identification}'), '{$id}', {$level});
                $(document).on('{$in_view_signal}', function(event, signalData) {
                    console.log('SlateSessionStateCode');
                });
                $(document).on('{$in_view_signal}', function(event, signalData) {
                     il.GS.Client.trigger('$id');
                });";
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
            case ($item instanceof Tool):
                $level = ItemState::LEVEL_OF_TOOL;
                break;
            case ($item instanceof isTopItem):
                $level = ItemState::LEVEL_OF_TOPITEM;
                break;
            default:
                $level = ItemState::LEVEL_OF_SUBITEM;
        }

        return $level;
    }
}
