<?php

declare(strict_types=1);

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
 * Class ilAbstractBuddySystemRelationStateButtonRenderer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractBuddySystemRelationStateButtonRenderer implements ilBuddySystemRelationStateButtonRenderer
{
    protected ilBuddySystemRelation $relation;
    protected int $usrId;
    protected ilTemplate $tpl;
    protected ilLanguage $lng;

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

    protected function getLanguageVariableSuffix(): string
    {
        $suffix = '_p';
        if ($this->relation->isOwnedByActor()) {
            $suffix = '_a';
        }

        return $suffix;
    }

    protected function render(): void
    {
        $this->renderStateButton();
        $states = $this->relation->getCurrentPossibleTargetStates();
        foreach ($states as $target_state) {
            $this->renderTargetState($target_state);
        }
    }

    protected function getTemplateVariablePrefix(): string
    {
        return '';
    }

    protected function renderStateButton(): void
    {
        $state_id = ilStr::convertUpperCamelCaseToUnderscoreCase($this->relation->getState()->getName());

        $this->tpl->setVariable(
            $this->getTemplateVariablePrefix() . 'BUTTON_TXT',
            $this->lng->txt(
                'buddy_bs_btn_txt_' . $state_id . $this->getLanguageVariableSuffix()
            )
        );
    }

    protected function renderTargetState(ilBuddySystemRelationState $target_state): void
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

    public function getHtml(): string
    {
        $this->render();

        return $this->tpl->get();
    }
}
