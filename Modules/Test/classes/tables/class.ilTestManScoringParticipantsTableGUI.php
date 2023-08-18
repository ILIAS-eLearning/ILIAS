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
*
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup	ModulesTest
*/

class ilTestManScoringParticipantsTableGUI extends ilTable2GUI
{
    public const PARENT_DEFAULT_CMD = 'showManScoringParticipantsTable';
    public const PARENT_APPLY_FILTER_CMD = 'applyManScoringParticipantsFilter';
    public const PARENT_RESET_FILTER_CMD = 'resetManScoringParticipantsFilter';

    public const PARENT_EDIT_SCORING_CMD = 'showManScoringParticipantScreen';

    public function __construct(ilTestScoringGUI $parent_obj)
    {
        $this->setPrefix('manScorePartTable');
        $this->setId('manScorePartTable');

        parent::__construct($parent_obj, self::PARENT_DEFAULT_CMD);

        $this->setFilterCommand(self::PARENT_APPLY_FILTER_CMD);
        $this->setResetCommand(self::PARENT_RESET_FILTER_CMD);

        $this->setFormName('manScorePartTable');
        $this->setStyle('table', 'fullwidth');

        $this->enable('header');

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, self::PARENT_DEFAULT_CMD));

        $this->setRowTemplate("tpl.il_as_tst_man_scoring_participant_tblrow.html", "Modules/Test");

        $this->initColumns();
        $this->initOrdering();

        $this->initFilter();
    }

    private function initColumns(): void
    {
        if ($this->parent_obj->getObject()->getAnonymity()) {
            $this->addColumn($this->lng->txt("name"), 'lastname', '100%');
        } else {
            $this->addColumn($this->lng->txt("lastname"), 'lastname', '');
            $this->addColumn($this->lng->txt("firstname"), 'firstname', '');
            $this->addColumn($this->lng->txt("login"), 'login', '');
        }

        $this->addColumn($this->lng->txt('actions'), '', '1%');
    }

    private function initOrdering(): void
    {
        $this->enable('sort');

        $this->setDefaultOrderField("lastname");
        $this->setDefaultOrderDirection("asc");
    }

    public function initFilter(): void
    {
        $this->setDisableFilterHiding(true);

        $participantStatus = new ilSelectInputGUI($this->lng->txt('tst_participant_status'), 'participant_status');

        $statusOptions = array();
        $statusOptions[ilTestScoringGUI::PART_FILTER_ALL_USERS] = $this->lng->txt("all_users");
        $statusOptions[ilTestScoringGUI::PART_FILTER_MANSCORING_NONE] = $this->lng->txt("manscoring_none");
        $statusOptions[ilTestScoringGUI::PART_FILTER_MANSCORING_DONE] = $this->lng->txt("manscoring_done");
        $statusOptions[ilTestScoringGUI::PART_FILTER_ACTIVE_ONLY] = $this->lng->txt("usr_active_only");
        $statusOptions[ilTestScoringGUI::PART_FILTER_INACTIVE_ONLY] = $this->lng->txt("usr_inactive_only");

        $participantStatus->setOptions($statusOptions);

        $this->addFilterItem($participantStatus);

        $participantStatus->readFromSession();

        if (!$participantStatus->getValue()) {
            $participantStatus->setValue((string) ilTestScoringGUI::PART_FILTER_MANSCORING_NONE);
        }
    }

    public function fillRow(array $a_set): void
    {
        $this->ctrl->setParameter($this->parent_obj, 'active_id', $a_set['active_id']);

        if (!$this->parent_obj->getObject()->getAnonymity()) {
            $this->tpl->setCurrentBlock('personal');
            $this->tpl->setVariable("PARTICIPANT_FIRSTNAME", $a_set['firstname']);
            $this->tpl->setVariable("PARTICIPANT_LOGIN", $a_set['login']);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("PARTICIPANT_LASTNAME", $a_set['lastname']);

        $this->tpl->setVariable("HREF_SCORE_PARTICIPANT", $this->ctrl->getLinkTarget($this->parent_obj, self::PARENT_EDIT_SCORING_CMD));
        $this->tpl->setVariable("TXT_SCORE_PARTICIPANT", $this->lng->txt('tst_edit_scoring'));
    }

    public function getInternalyOrderedDataValues(): array
    {
        $this->determineOffsetAndOrder();

        return ilArrayUtil::sortArray(
            $this->getData(),
            $this->getOrderField(),
            $this->getOrderDirection(),
            $this->numericOrdering($this->getOrderField())
        );
    }
}
