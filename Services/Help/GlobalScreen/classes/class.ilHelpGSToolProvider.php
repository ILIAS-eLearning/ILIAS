<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy as LegacySlate;

/**
 * Class ilHelpGSToolProvider
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelpGSToolProvider extends AbstractDynamicToolProvider
{
    use ilHelpDisplayed;
    use Hasher;

    public const SHOW_HELP_TOOL = 'show_help_tool';

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }

    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        global $DIC;

        /** @var ilHelpGUI $help_gui */
        $help_gui = $DIC["ilHelp"];

        $lng = $DIC->language();
        $lng->loadLanguageModule("help");
        $f = $DIC->ui()->factory();

        $tools = [];

        $hidden = !$help_gui->isHelpPageActive();

        $title = $lng->txt("help");
        $icon = $f->symbol()->icon()->standard("hlps", $title);

        if ($this->showHelpTool()) {
            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id, true);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };

            $identification = $iff("help");
            $hashed = $this->hash($identification->serialize());
            $tools[] = $this->factory->tool($identification)
                                            ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) use ($hashed, $hidden) : ILIAS\UI\Component\Component {
                                                if ($c instanceof LegacySlate) {
                                                    $signal_id = $c->getToggleSignal()->getId();
                                                    return $c->withAdditionalOnLoadCode(static function ($id) use ($hashed) {
                                                        return "
                                                 $('body').on('il-help-toggle-slate', function(){
                                                    if (!$('#$id').hasClass('disengaged')) {
                                                        il.Help.resetCurrentPage();
                                                        il.UI.maincontrols.mainbar.removeTool('$hashed');
                                                    } else {
                                                        il.UI.maincontrols.mainbar.engageTool('$hashed');
                                                    }
                                                 });";
                                                    });
                                                }
                                                return $c;
                                            })
                                     ->withInitiallyHidden($hidden)
                                     ->withTitle($title)
                                     ->withSymbol($icon)
                                     ->withContentWrapper(function () use ($l) {
                                         return $l($this->getHelpContent());
                                     })
                                     ->withPosition(90);
        }

        return $tools;
    }

    private function getHelpContent() : string
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $main_tpl = $DIC->ui()->mainTemplate();

        /** @var ilHelpGUI $help_gui */
        $help_gui = $DIC["ilHelp"];

        $help_gui->initHelp($main_tpl, $ctrl->getLinkTargetByClass("ilhelpgui", "", "", true));

        $html = "";
        if ((defined("OH_REF_ID") && (int) OH_REF_ID > 0) || (defined('DEVMODE') && (int) DEVMODE === 1)) {
            $html = "<div class='ilHighlighted small'>Screen ID: " . $help_gui->getScreenId() . "</div>";
        }

        $html .= "<div id='ilHelpPanel'>&nbsp;</div>";

        return $html;
    }
}
