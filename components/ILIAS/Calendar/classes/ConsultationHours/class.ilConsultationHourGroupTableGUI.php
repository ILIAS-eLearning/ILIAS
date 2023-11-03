<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Implementation\Factory as UIImplementationFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * Description of class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourGroupTableGUI extends ilTable2GUI
{
    private int $user_id = 0;

    private UIRenderer $renderer;
    private UIImplementationFactory $uiFactory;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_user_id)
    {
        global $DIC;

        $this->user_id = $a_user_id;
        $this->setId('chgrp_' . $this->user_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->renderer = $DIC->ui()->renderer();
        $this->uiFactory = $DIC->ui()->factory();

        $this->initTable();
    }

    /**
     * Init table
     */
    protected function initTable(): void
    {
        $this->setRowTemplate('tpl.ch_group_row.html', 'Services/Calendar');

        $this->setTitle($this->lng->txt('cal_ch_grps'));
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));

        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('cal_ch_assigned_apps'), 'apps');
        $this->addColumn($this->lng->txt('cal_ch_max_books'), 'max_books');
        $this->addColumn($this->lng->txt('actions'), '');

        $this->enable('sort');
        $this->enable('header');
        $this->enable('num_info');
        $this->setDefaultOrderField('title');
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable('MAX_BOOKINGS', $a_set['max_books']);
        $this->tpl->setVariable('ASSIGNED', $a_set['assigned']);

        $dropDownItems[] = array();

        $this->ctrl->setParameter($this->getParentObject(), 'grp_id', $a_set['id']);

        $dropDownItems[] = $this->uiFactory->button()->shy(
            $this->lng->txt('edit'),
            $this->ctrl->getLinkTarget($this->getParentObject(), 'editGroup')
        );

        // add members
        if ($a_set['assigned']) {
            $dropDownItems[] = $this->uiFactory->button()->shy(
                $this->lng->txt('cal_ch_assign_participants'),
                $this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI', '')
            );
        }

        $dropDownItems[] = $this->uiFactory->button()->shy(
            $this->lng->txt('delete'),
            $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteGroup')
        );
        $dropDown = $this->uiFactory->dropdown()->standard($dropDownItems)
                ->withLabel($this->lng->txt('actions'));
        $this->tpl->setVariable('ACTIONS', $this->renderer->render($dropDown));
    }

    /**
     * Parse Groups
     * @param ilConsultationHourGroup[] $groups
     */
    public function parse(array $groups): void
    {
        $rows = array();
        $counter = 0;
        foreach ($groups as $group) {
            $rows[$counter]['id'] = $group->getGroupId();
            $rows[$counter]['title'] = $group->getTitle();
            $rows[$counter]['max_books'] = $group->getMaxAssignments();
            $rows[$counter]['assigned'] = count(
                ilConsultationHourAppointments::getAppointmentIdsByGroup(
                    $this->user_id,
                    $group->getGroupId(),
                    null
                )
            );
            ++$counter;
        }
        $this->setData($rows);
    }
}
