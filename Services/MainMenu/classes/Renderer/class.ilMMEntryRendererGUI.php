<?php

/**
 * Class ilMMEntryRendererGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMEntryRendererGUI
{

    /**
     * @return string
     * @throws Throwable
     * @throws ilTemplateException
     */
    public function getHTML() : string
    {
        $html = "";

        // Plugin-Slot
        $uip = new ilUIHookProcessor(
            "Services/MainMenu",
            "main_menu_list_entries",
            array("main_menu_gui" => $this)
        );

        if (!$uip->replaced()) {
            $html = $this->render();
        }

        $html = $uip->getHTML($html);

        return $html;
    }


    /**
     * @return string
     * @throws Throwable
     * @throws ilTemplateException
     */
    protected function render() : string
    {
        global $DIC;

        $top_items = (new ilMMItemRepository())->getStackedTopItemsForPresentation();
        $tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');
        /**
         * @var $top_item \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem|\ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem
         */
        $components = [];

        foreach ($top_items as $top_item) {
            $components[] = $top_item->getTypeInformation()->getRenderer()->getComponentForItem($top_item);
        }

        $tpl->setVariable("ENTRIES", $DIC->ui()->renderer()->render($components));

        return $tpl->get();
    }
}
