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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestParticipantsTableGUI extends ilTable2GUI
{
    protected bool $manageResultsCommandsEnabled = false;
    protected bool $manageInviteesCommandsEnabled = false;

    protected ?string $rowKeyDataField;

    protected bool $anonymity;
    protected array $filter;

    protected bool $participantHasSolutionsFilterEnabled = false;

    public function __construct(
        ilTestParticipantsGUI $parent_obj,
        string $parent_cmd,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer
    ) {
        $this->setId('tst_participants_' . $parent_obj->getTestObj()->getRefId());

        parent::__construct($parent_obj, $parent_cmd);

        $this->setStyle('table', 'fullwidth');

        $this->setFormName('participantsForm');
        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));

        $this->setRowTemplate("tpl.il_as_tst_participants_row.html", "Modules/Test");

        $this->enable('header');
        $this->enable('sort');

        $this->setShowRowsSelector(true);
    }

    public function isManageResultsCommandsEnabled(): bool
    {
        return $this->manageResultsCommandsEnabled;
    }

    public function setManageResultsCommandsEnabled(bool $manageResultsCommandsEnabled): void
    {
        $this->manageResultsCommandsEnabled = $manageResultsCommandsEnabled;
    }

    public function isManageInviteesCommandsEnabled(): bool
    {
        return $this->manageInviteesCommandsEnabled;
    }

    public function setManageInviteesCommandsEnabled(bool $manageInviteesCommandsEnabled): void
    {
        $this->manageInviteesCommandsEnabled = $manageInviteesCommandsEnabled;

        if ($manageInviteesCommandsEnabled) {
            $this->setSelectAllCheckbox('chbUser');
        } else {
            $this->setSelectAllCheckbox('');
        }
    }

    public function getRowKeyDataField(): string
    {
        return $this->rowKeyDataField;
    }

    public function setRowKeyDataField(string $rowKeyDataField): void
    {
        $this->rowKeyDataField = $rowKeyDataField;
    }

    public function getAnonymity()
    {
        return $this->anonymity;
    }

    public function setAnonymity($anonymity): void
    {
        $this->anonymity = $anonymity;
    }

    public function isParticipantHasSolutionsFilterEnabled(): bool
    {
        return $this->participantHasSolutionsFilterEnabled;
    }


    public function setParticipantHasSolutionsFilterEnabled(bool $participantHasSolutionsFilterEnabled): void
    {
        $this->participantHasSolutionsFilterEnabled = $participantHasSolutionsFilterEnabled;
    }

    public function numericOrdering(string $a_field): bool
    {
        return in_array($a_field, array(
            'access', 'tries'
        ));
    }

    protected function needsCheckboxColumn(): bool
    {
        return $this->isManageInviteesCommandsEnabled();
    }

    public function initColumns(): void
    {
        if ($this->needsCheckboxColumn()) {
            $this->addColumn('', '', '1%');
        }

        $this->addColumn($this->lng->txt("name"), 'name', '');
        $this->addColumn($this->lng->txt("login"), 'login', '');

        if ($this->isManageInviteesCommandsEnabled()) {
            $this->addColumn($this->lng->txt("clientip"), 'clientip', '');
        }

        $this->addColumn($this->lng->txt("tst_started"), 'started', '');
        $this->addColumn($this->lng->txt("tst_nr_of_tries_of_user"), 'tries', '');

        $this->addColumn($this->lng->txt("unfinished_passes"), 'unfinished', '');
        $this->addColumn($this->lng->txt("tst_finished"), 'finished', '');

        $this->addColumn($this->lng->txt("last_access"), 'access', '');

        if ($this->isActionsColumnRequired()) {
            $this->addColumn('', '', '');
        }
    }

    public function initCommands(): void
    {
        if ($this->isManageInviteesCommandsEnabled()) {
            $this->addMultiCommand('saveClientIp', $this->lng->txt('save'));
            $this->addMultiCommand('removeParticipants', $this->lng->txt('remove_as_participant'));
        }
    }

    public function initFilter(): void
    {
        if ($this->isParticipantHasSolutionsFilterEnabled()) {
            $ti = new ilSelectInputGUI($this->lng->txt("selection"), "selection");
            $ti->setOptions(
                array(
                    'all' => $this->lng->txt('all_participants'),
                    'withSolutions' => $this->lng->txt('with_solutions_participants'),
                    'withoutSolutions' => $this->lng->txt('without_solutions_participants')
                )
            );
            $this->addFilterItem($ti);
            $ti->readFromSession();        // get currenty value from session (always after addFilterItem())
            $this->filter['title'] = $ti->getValue();
        }
    }

    public function fillRow(array $a_set): void
    {
        if ($this->needsCheckboxColumn()) {
            $this->tpl->setCurrentBlock('checkbox_column');
            $this->tpl->setVariable("CHB_ROW_KEY", $this->fetchRowKey($a_set));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isManageInviteesCommandsEnabled()) {
            $this->tpl->setCurrentBlock('client_ip_column');
            $this->tpl->setVariable("CLIENT_IP", $a_set['clientip']);
            $this->tpl->setVariable("CIP_ROW_KEY", $this->fetchRowKey($a_set));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isActionsColumnRequired()) {
            $this->tpl->setCurrentBlock('actions_column');

            if ($a_set['active_id'] > 0) {
                $this->tpl->setVariable('ACTIONS', $this->buildActionsMenu($a_set));
            } else {
                $this->tpl->touchBlock('actions_column');
            }

            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ROW_KEY", $this->fetchRowKey($a_set));
        $this->tpl->setVariable("LOGIN", $a_set['login']);
        $this->tpl->setVariable("FULLNAME", $a_set['name']);

        $this->tpl->setVariable("STARTED", ($a_set['started']) ? $this->buildOkIcon() : '');
        $this->tpl->setVariable("TRIES", $this->fetchTriesValue($a_set));
        $this->tpl->setVariable("UNFINISHED_PASSES", $this->buildUnfinishedPassesStatusString($a_set));

        $this->tpl->setVariable("FINISHED", ($a_set['finished']) ? $this->buildOkIcon() : '');
        $this->tpl->setVariable("ACCESS", $this->buildFormattedAccessDate($a_set));
    }

    protected function buildActionsMenu(array $data): string
    {
        $this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
        $actions = [];
        if ($this->isManageResultsCommandsEnabled() && $data['unfinished']) {
            $finishHref = $this->ctrl->getLinkTargetByClass('ilTestEvaluationGUI', 'finishTestPassForSingleUser');
            $actions[] = $this->ui_factory->link()->standard($this->lng->txt('finish_test'), $finishHref);
        }
        $dropdown = $this->ui_factory->dropdown()->standard($actions);
        return $this->ui_renderer->render($dropdown);
    }

    protected function isActionsColumnRequired(): bool
    {
        if ($this->isManageResultsCommandsEnabled()) {
            return true;
        }

        return false;
    }

    protected function fetchRowKey(array $data): string
    {
        return (string) $data[$this->getRowKeyDataField()];
    }

    protected function fetchTriesValue(array $data): string
    {
        if ($data['tries'] < 1) {
            return '';
        }

        if ($data['tries'] > 1) {
            return sprintf($this->lng->txt("passes_finished"), $data['tries']);
        }

        return sprintf($this->lng->txt("pass_finished"), $data['tries']);
    }

    protected function buildUnfinishedPassesStatusString(array $data): string
    {
        if ($data['unfinished']) {
            return $this->lng->txt('yes');
        }

        return $this->lng->txt('no');
    }

    protected function buildOkIcon(): string
    {
        return $this->ui_renderer->render($this->ui_factory->symbol()->icon()->custom(
            ilUtil::getImagePath("standard/icon_ok.svg"),
            $this->lng->txt("ok")
        ));
    }

    protected function buildFormattedAccessDate(array $data): string
    {
        return ilDatePresentation::formatDate(new ilDateTime($data['access'], IL_CAL_DATETIME));
    }
}
