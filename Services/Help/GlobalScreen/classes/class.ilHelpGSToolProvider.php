<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Class ilHelpGSToolProvider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilHelpGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_HELP_TOOL = 'show_help_tool';


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        global $DIC;


        $lng = $DIC->language();
        $lng->loadLanguageModule("help");
        $f = $DIC->ui()->factory();

        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();

        $title = $lng->txt("help");
        $icon = $f->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/info.svg"), $title);

        if ($this->showHelpTool()) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };

            $tools[] = $this->factory->tool($iff("help"))
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContent($l($this->getHelpContent()))
                ->withPosition(90);
        }

        return $tools;
    }

    /**
     * Show help tool?
     *
     * @param
     * @return
     */
    protected function showHelpTool(): bool
    {
        global $DIC;

        $user = $DIC->user();
        $settings = $DIC->settings();

        if ($user->getLanguage() != "de")
        {
            return false;
        }

        if (ilSession::get("show_help_tool") != "1") {
            return false;
        }

        if ($settings->get("help_mode") == "2")
        {
            return false;
        }

        if ((defined("OH_REF_ID") && OH_REF_ID > 0))
        {
            true;
        }
        else
        {
            $module = (int) $settings->get("help_module");
            if ($module == 0)
            {
                return false;
            }
        }
        return true;
    }



    /**
     * help
     *
     * @param int $ref_id
     * @return string
     */
    private function getHelpContent() : string
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $main_tpl = $DIC->ui()->mainTemplate();

        /** @var ilHelpGUI $help_gui */
        $help_gui = $DIC["ilHelp"];

        $help_gui->initHelp($main_tpl, $ctrl->getLinkTargetByClass("ilhelpgui", "", "", true));

        $html = "";
        if ((defined("OH_REF_ID") && OH_REF_ID > 0) || DEVMODE == 1) {
            $html = "<div class='ilHighlighted small'>Screen ID: ".$help_gui->getScreenId()."</div>";
        }

        $html.= "<div id='ilHelpPanel'>&nbsp;</div>";

        return $html;
    }
}
