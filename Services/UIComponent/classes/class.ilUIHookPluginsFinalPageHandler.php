<?php

use ILIAS\GlobalScreen\Scope\Layout\FinalPageHandler;
use ILIAS\GlobalScreen\Scope\Layout\ModifierServices;

/**
 * Class ilUIHookPluginsFinalPageHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilUIHookPluginsFinalPageHandler implements FinalPageHandler
{

    /**
     * @inheritDoc
     */
    public function handle(ModifierServices $modifier_services) : void
    {
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];

        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");

        foreach ($pl_names as $pl) {
            /**
             * @var $ui_plugin ilUserInterfaceHookPlugin
             */
            $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            $gui_class = $ui_plugin->getUIClassInstance();
            $gui_class->modifyGlobalLayout($modifier_services);
        }
    }
}
