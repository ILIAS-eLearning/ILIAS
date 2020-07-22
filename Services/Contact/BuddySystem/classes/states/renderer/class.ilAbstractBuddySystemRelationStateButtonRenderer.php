<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/interfaces/interface.ilBuddySystemRelationStateButtonRenderer.php';
require_once 'Services/Utilities/classes/class.ilStr.php';

/**
 * Class ilAbstractBuddySystemRelationStateButtonRenderer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractBuddySystemRelationStateButtonRenderer implements ilBuddySystemRelationStateButtonRenderer
{
    /**
     * @var ilBuddySystemRelation
     */
    protected $relation;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @param int                   $user_id
     * @param ilBuddySystemRelation $relation
     */
    public function __construct($user_id, ilBuddySystemRelation $relation)
    {
        global $DIC;

        $this->user_id = $user_id;
        $this->relation = $relation;

        $this->tpl = new ilTemplate(
            'tpl.buddy_system_state_' . ilStr::convertUpperCamelCaseToUnderscoreCase($this->relation->getState()->getName()) . '.html',
            true,
            true,
            'Services/Contact/BuddySystem'
        );

        $this->lng = $DIC['lng'];
    }

    /**
     * @return string
     */
    protected function getLanguageVariableSuffix()
    {
        if ($this->relation->isOwnedByRequest()) {
            $suffix = '_a';
        } else {
            $suffix = '_p';
        }
        return $suffix;
    }

    /**
     *
     */
    protected function render()
    {
        $this->renderStateButton();
        $states = $this->relation->getCurrentPossibleTargetStates();
        foreach ($states as $target_state) {
            $this->renderTargetState($target_state);
        }
    }

    /**
     * @return string
     */
    protected function getTemplateVariablePrefix()
    {
        return '';
    }

    /**
     *
     */
    protected function renderStateButton()
    {
        $state_id = ilStr::convertUpperCamelCaseToUnderscoreCase($this->relation->getState()->getName());

        $this->tpl->setVariable(
            $this->getTemplateVariablePrefix() . 'BUTTON_TXT',
            $this->lng->txt(
                'buddy_bs_btn_txt_' . $state_id . $this->getLanguageVariableSuffix()
            )
        );
    }

    /**
     * @param ilBuddySystemRelationState $target_state
     */
    protected function renderTargetState(ilBuddySystemRelationState $target_state)
    {
        $state_id = ilStr::convertUpperCamelCaseToUnderscoreCase($this->relation->getState()->getName());
        $target_state_id = ilStr::convertUpperCamelCaseToUnderscoreCase($target_state->getName());

        $this->tpl->setVariable(
            $this->getTemplateVariablePrefix() . 'TARGET_STATE_' . strtoupper($target_state_id),
            get_class($target_state)
        );
        $this->tpl->setVariable(
            $this->getTemplateVariablePrefix() . 'TARGET_STATE_ACTION_' . strtoupper($target_state_id),
            $target_state->getAction()
        );
        $this->tpl->setVariable(
            $this->getTemplateVariablePrefix() . 'TARGET_STATE_TXT_' . strtoupper($target_state_id),
            $this->lng->txt(
                'buddy_bs_act_btn_txt_' . $state_id . '_to_' . $target_state_id
            )
        );
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $this->render();
        return $this->tpl->get();
    }
}
