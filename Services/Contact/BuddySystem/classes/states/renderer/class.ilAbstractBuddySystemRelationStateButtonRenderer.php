<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAbstractBuddySystemRelationStateButtonRenderer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractBuddySystemRelationStateButtonRenderer implements ilBuddySystemRelationStateButtonRenderer
{
    /** @var ilBuddySystemRelation */
    protected $relation;

    /** @var int */
    protected $usrId;

    /** @var ilTemplate */
    protected $tpl;

    /** @var ilLanguage */
    protected $lng;

    /**
     * @param int $usrId
     * @param ilBuddySystemRelation $relation
     */
    public function __construct(int $usrId, ilBuddySystemRelation $relation)
    {
        global $DIC;

        $this->usrId = $usrId;
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
    protected function getLanguageVariableSuffix() : string 
    {
        $suffix = '_p';
        if ($this->relation->isOwnedByActor()) {
            $suffix = '_a';
        }

        return $suffix;
    }

    /**
     *
     */
    protected function render() : void 
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
    protected function getTemplateVariablePrefix() : string
    {
        return '';
    }

    /**
     *
     */
    protected function renderStateButton() : void
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
    protected function renderTargetState(ilBuddySystemRelationState $target_state) : void
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
    public function getHtml() : string 
    {
        $this->render();

        return $this->tpl->get();
    }
}