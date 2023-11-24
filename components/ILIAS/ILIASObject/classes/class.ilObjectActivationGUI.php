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

declare(strict_types=1);

/**
 * Class ilObjectActivationGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilObjectActivationGUI: ilConditionHandlerGUI
 */
class ilObjectActivationGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs_gui;
    protected ilHelpGUI $help;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected int $parent_ref_id;
    protected int $item_id;

    protected ?int $timing_mode = null;
    protected ?ilObjectActivation $activation = null;

    public function __construct(int $ref_id, int $item_id)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('crs');
        $this->tabs_gui = $DIC->tabs();
        $this->help = $DIC["ilHelp"];
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();

        $this->parent_ref_id = $ref_id;
        $this->item_id = $item_id;

        $this->ctrl->saveParameter($this, 'item_id');
    }

    public function executeCommand(): void
    {
        $this->setTabs();

        $this->tpl->loadStandardTemplate();

        $this->ctrl->forwardCommand(
            new ilConditionHandlerGUI($this->item_id)
        );
        $this->tpl->printToStdout();
    }

    protected function setTabs(): bool
    {
        $this->tabs_gui->clearTargets();

        $this->help->setScreenIdComponent("obj");

        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->parent_ref_id);
        $back_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", "");
        $ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->string());
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
        $this->tabs_gui->setBackTarget($this->lng->txt('btn_back'), $back_link);

        $this->ctrl->setParameterByClass('ilconditionhandlergui', 'item_id', $this->item_id);
        $this->tabs_gui->addTarget(
            "preconditions",
            $this->ctrl->getLinkTargetByClass('ilConditionHandlerGUI', 'listConditions'),
            "",
            "ilConditionHandlerGUI"
        );
        return true;
    }
}
