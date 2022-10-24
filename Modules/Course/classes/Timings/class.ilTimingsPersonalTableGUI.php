<?php

declare(strict_types=0);

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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPServices;

/**
 * TableGUI class for editing personal timings
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesCourse
 */
class ilTimingsPersonalTableGUI extends ilTable2GUI
{
    private ilObject $container;
    private ilObjCourse $main_container;
    private int $user_id = 0;
    private bool $failure = false;

    protected Refinery $refinery;
    protected HTTPServices $http;

    public function __construct(
        object $a_parent_class,
        string $a_parent_cmd,
        ilObject $a_container_obj,
        ilObjCourse $a_main_container
    ) {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->container = $a_container_obj;
        $this->main_container = $a_main_container;
        $this->setId('personal_timings_' . $this->getContainerObject()->getRefId());
        parent::__construct($a_parent_class, $a_parent_cmd);
    }

    public function getContainerObject(): ilObject
    {
        return $this->container;
    }

    public function getMainContainer(): ilObjCourse
    {
        return $this->main_container;
    }

    public function setUserId(int $a_usr_id): void
    {
        $this->user_id = $a_usr_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function init(): void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.crs_personal_timings_row.html', 'Modules/Course');
        $this->setTitle($this->lng->txt('crs_timings_edit_personal'));
        $this->addColumn($this->lng->txt('title'), '', '40%');
        $this->addColumn($this->lng->txt('crs_timings_short_start_end'), '');
        $this->addColumn($this->lng->txt('crs_timings_short_end'), '');
        $this->addColumn($this->lng->txt('crs_timings_short_changeable'), '');
        $this->addCommandButton('updatePersonalTimings', $this->lng->txt('save'));
        $this->setShowRowsSelector(false);
    }

    public function setFailureStatus(bool $a_status): void
    {
        $this->failure = $a_status;
    }

    public function getFailureStatus(): bool
    {
        return $this->failure;
    }

    protected function fillRow(array $a_set): void
    {
        if ($a_set['error'] ?? false) {
            $this->tpl->setVariable('TD_CLASS', 'warning');
        } else {
            $this->tpl->setVariable('TD_CLASS', 'std');
        }

        // title
        if (strlen($a_set['title_link'] ?? '')) {
            $this->tpl->setCurrentBlock('title_link');
            $this->tpl->setVariable('TITLE_LINK', $a_set['title_link']);
            $this->tpl->setVariable('TITLE_LINK_NAME', $a_set['title']);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('title_plain');
            $this->tpl->setVariable('TITLE', $a_set['title']);
            $this->tpl->parseCurrentBlock();
        }
        if (strlen($a_set['desc'] ?? '')) {
            $this->tpl->setCurrentBlock('item_description');
            $this->tpl->setVariable('DESC', $a_set['desc']);
            $this->tpl->parseCurrentBlock();
        }
        if ($a_set['failure'] ?? false) {
            $this->tpl->setCurrentBlock('alert');
            $this->tpl->setVariable('IMG_ALERT', ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable('ALT_ALERT', $this->lng->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", $this->lng->txt($a_set['failure']));
            $this->tpl->parseCurrentBlock();
        }

        // active
        $this->tpl->setVariable('NAME_ACTIVE', 'item[' . $a_set['ref_id'] . '][active]');
        $this->tpl->setVariable(
            'CHECKED_ACTIVE',
            ($a_set['item']['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) ? 'checked="checked"' : ''
        );

        $error_post_item = (array) ($this->http->request()->getParsedBody()['item'] ?? []);

        // start
        $dt_input = new ilDateTimeInputGUI('', 'item[' . $a_set['ref_id'] . '][sug_start]');
        $dt_input->setDate(new ilDate($a_set['item']['suggestion_start'], IL_CAL_UNIX));
        if ($this->getFailureStatus()) {
            $dt_input->setDate(new ilDate($error_post_item[$a_set['ref_id']]['sug_start'] ?? '', IL_CAL_DATE));
        }

        if (!($a_set['item']['changeable'] ?? false)) {
            $dt_input->setDisabled(true);
        }

        $this->tpl->setVariable('start_abs');
        $this->tpl->setVariable('SUG_START', $dt_input->render());
        $this->tpl->parseCurrentBlock();

        // end
        $dt_end = new ilDateTimeInputGUI('', 'item[' . $a_set['ref_id'] . '][sug_end]');
        $dt_end->setDate(new ilDate($a_set['item']['suggestion_end'], IL_CAL_UNIX));
        if ($this->getFailureStatus()) {
            $dt_end->setDate(new ilDate($error_post_item[$a_set['ref_id']]['sug_end'] ?? '', IL_CAL_DATE));
        }

        if (!($a_set['item']['changeable'] ?? false)) {
            $dt_end->setDisabled(true);
        }
        $this->tpl->setVariable('end_abs');
        $this->tpl->setVariable('SUG_END', $dt_end->render());
        $this->tpl->parseCurrentBlock();

        // changeable
        $this->tpl->setVariable(
            'TXT_CHANGEABLE',
            $a_set['item']['changeable'] ? $this->lng->txt('yes') : $this->lng->txt('no')
        );
    }

    public function parse(array $a_item_data, array $failed = array()): void
    {
        $rows = array();
        foreach ($a_item_data as $item) {
            // hide objects without timings
            if ($item['timing_type'] != ilObjectActivation::TIMINGS_PRESETTING) {
                continue;
            }

            $current_row = array();

            // no item groups
            if ($item['type'] == 'itgr') {
                continue;
            }
            $current_row['ref_id'] = $item['ref_id'];

            $current_row = $this->parseTitle($current_row, $item);

            $item = $this->parseUserTimings($item);
            $current_row['start'] = $item['suggestion_start'];

            if (array_key_exists($item['ref_id'], $failed)) {
                $current_row['failed'] = true;
                $current_row['failure'] = $failed[$item['ref_id']];
            }
            $current_row['item'] = $item;
            $rows[] = $current_row;
        }
        // stable sort first title, second start
        $rows = ilArrayUtil::sortArray($rows, 'title', 'asc', false);
        $rows = ilArrayUtil::sortArray($rows, 'start', 'asc', true);
        $this->setData($rows);
    }

    protected function parseUserTimings(array $a_item): array
    {
        $tu = new ilTimingUser($a_item['child'], $this->getUserId());

        if ($a_item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) {
            if ($tu->getStart()->get(IL_CAL_UNIX)) {
                $a_item['suggestion_start'] = $tu->getStart()->get(IL_CAL_UNIX);
            }
            if ($tu->getEnd()->get(IL_CAL_UNIX)) {
                $a_item['suggestion_end'] = $tu->getEnd()->get(IL_CAL_UNIX);
            }
        }
        return $a_item;
    }

    protected function parseTitle(array $current_row, array $item): array
    {
        switch ($item['type']) {
            case 'fold':
            case 'grp':
                $current_row['title'] = $item['title'];
                $current_row['title_link'] = ilLink::_getLink($item['ref_id'], $item['type']);
                break;

            case 'sess':
                if (strlen($item['title'])) {
                    $current_row['title'] = $item['title'];
                } else {
                    $app_info = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item['ref_id']));
                    $current_row['title'] = ilSessionAppointment::_appointmentToString(
                        $app_info['start'],
                        $app_info['end'],
                        (bool) $app_info['fullday']
                    );
                }
                $current_row['title_link'] = ilLink::_getLink($item['ref_id'], $item['type']);
                break;

            default:
                $current_row['title'] = $item['title'];
                $current_row['title_link'] = '';
                break;

        }
        $current_row['desc'] = $item['desc'];

        return $current_row;
    }
}
