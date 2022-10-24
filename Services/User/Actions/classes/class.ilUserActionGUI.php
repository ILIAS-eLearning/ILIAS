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

/**
 * A class that provides a collection of actions on users
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilUserActionContext $user_action_context;
    protected bool $init_done = false;
    protected int $current_user_id;

    protected function __construct(
        ilUserActionContext $a_user_action_context,
        ilGlobalTemplateInterface $a_global_tpl,
        int $a_current_user_id
    ) {
        $this->tpl = $a_global_tpl;
        $this->user_action_context = $a_user_action_context;
        $this->current_user_id = $a_current_user_id;
    }

    public static function getInstance(
        ilUserActionContext $a_user_action_context,
        ilGlobalTemplateInterface $a_global_tpl,
        int $a_current_user_id
    ): ilUserActionGUI {
        return new ilUserActionGUI($a_user_action_context, $a_global_tpl, $a_current_user_id);
    }

    public function init(): void
    {
        $tpl = $this->tpl;

        foreach (ilUserActionProviderFactory::getAllProviders() as $prov) {
            foreach ($prov->getActionTypes() as $act_type => $txt) {
                if (ilUserActionAdmin::lookupActive(
                    $this->user_action_context->getComponentId(),
                    $this->user_action_context->getContextId(),
                    $prov->getComponentId(),
                    $act_type
                )) {
                    foreach ($prov->getJsScripts($act_type) as $script) {
                        $tpl->addJavaScript($script);
                    }
                }
            }
        }
    }

    public function renderDropDown(int $a_target_user_id): string
    {
        if (!$this->init_done) {
            $this->init();
        }
        $act_collector = ilUserActionCollector::getInstance($this->current_user_id, $this->user_action_context);
        $action_collection = $act_collector->getActionsForTargetUser($a_target_user_id);
        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle("");
        $list->setPullRight(false);
        foreach ($action_collection->getActions() as $action) {
            $list->addItem($action->getText(), "", $action->getHref(), "", "", "", "", false, "", "", "", "", true, $action->getData());
        }
        return $list->getHTML();
    }
}
