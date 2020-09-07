<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * A class that provides a collection of actions on users
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilUserActionContext
     */
    protected $user_action_context;

    /**
     * @var bool
     */
    protected $init_done = false;

    /**
     * @var int
     */
    protected $current_user_id;

    /**
     * Constructor
     *
     * @param ilUserActionContext $a_user_action_context
     * @param ilTemplate $a_global_tpl
     * @param int $a_current_user_id
     */
    protected function __construct(
        ilUserActionContext $a_user_action_context,
        ilTemplate $a_global_tpl,
        $a_current_user_id
    ) {
        $this->tpl = $a_global_tpl;
        $this->user_action_context = $a_user_action_context;
        $this->current_user_id = $a_current_user_id;
    }

    /**
     * Get instance
     *
     * @param ilUserActionContext $a_user_action_context
     * @param ilTemplate $a_global_tpl
     * @param int $a_current_user_id
     * @return ilUserActionGUI
     */
    public static function getInstance(ilUserActionContext $a_user_action_context, ilTemplate $a_global_tpl, $a_current_user_id)
    {
        return new ilUserActionGUI($a_user_action_context, $a_global_tpl, $a_current_user_id);
    }

    /**
     * Add requried js/css for an action context
     */
    public function init()
    {
        $tpl = $this->tpl;

        include_once("./Services/User/Actions/classes/class.ilUserActionAdmin.php");
        include_once("./Services/User/Actions/classes/class.ilUserActionProviderFactory.php");
        foreach (ilUserActionProviderFactory::getAllProviders() as $prov) {
            foreach ($prov->getActionTypes() as $act_type => $txt) {
                if (ilUserActionAdmin::lookupActive(
                    $this->user_action_context->getComponentId(),
                    $this->user_action_context->getContextId(),
                    $prov->getComponentId(),
                    $act_type
                )) {
                    foreach ($prov->getJsScripts($act_type) as $script) {
                        $tpl->addJavascript($script);
                    }
                    foreach ($prov->getCssFiles($act_type) as $file) {
                        $tpl->addCss($file);
                    }
                }
            }
        }
    }

    /**
     * Render drop down
     *
     * @param int $a_user_id target user id
     * @return string
     */
    public function renderDropDown($a_target_user_id)
    {
        if (!$this->init_done) {
            $this->init();
        }
        include_once("./Services/User/Gallery/classes/class.ilGalleryUserActionContext.php");
        include_once("./Services/User/Actions/classes/class.ilUserActionCollector.php");
        $act_collector = ilUserActionCollector::getInstance($this->current_user_id, $this->user_action_context);
        $action_collection = $act_collector->getActionsForTargetUser($a_target_user_id);
        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle("");
        foreach ($action_collection->getActions() as $action) {
            $list->addItem($action->getText(), "", $action->getHref(), "", "", "", "", false, "", "", "", "", true, $action->getData());
        }
        return $list->getHTML();
    }
}
