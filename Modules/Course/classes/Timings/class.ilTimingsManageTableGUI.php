<?php declare(strict_types=0);
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
 * TableGUI class for timings administration
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesCourse
 */
class ilTimingsManageTableGUI extends ilTable2GUI
{
    private ilObject $container;
    private ilObjCourse $main_container;
    private bool $failure = false;

    protected Refinery $refinery;
    protected HTTPServices $http;


    /**
     * Constructor
     */
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
        $this->setId('manage_timings_' . $this->getContainerObject()->getRefId());
        parent::__construct($a_parent_class, $a_parent_cmd);
    }

    public function getContainerObject() : ilObject
    {
        return $this->container;
    }

    public function getMainContainer() : ilObjCourse
    {
        return $this->main_container;
    }

    /**
     * Init table
     */
    public function init() : void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.crs_manage_timings_row.html', 'Modules/Course');

        $this->setTitle($this->lng->txt('edit_timings_list'));

        $this->addColumn($this->lng->txt('title'), '', '40%');
        $this->addColumn($this->lng->txt('crs_timings_short_active'), '', '', false);

        if ($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_RELATIVE) {
            $this->addColumn($this->lng->txt('crs_timings_short_start_end_rel'), '', '', false);
            $this->addColumn($this->lng->txt('crs_timings_time_frame'), '', '', false);
        } else {
            $this->addColumn($this->lng->txt('crs_timings_short_start_end'), '', '', false);
            $this->addColumn($this->lng->txt('crs_timings_short_end'), '');
        }
        $this->addColumn($this->lng->txt('crs_timings_short_changeable'), '', '', false);
        $this->addCommandButton('updateManagedTimings', $this->lng->txt('save'));
        $this->setShowRowsSelector(false);
    }

    public function setFailureStatus(bool $a_status) : void
    {
        $this->failure = $a_status;
    }

    public function getFailureStatus() : bool
    {
        return $this->failure;
    }

    protected function fillRow(array $a_set) : void
    {
        if ($a_set['error'] ?? false) {
            $this->tpl->setVariable('TD_CLASS', 'warning');
        } else {
            $this->tpl->setVariable('TD_CLASS', 'std');
        }

        // title
        if (strlen($a_set['title_link'] ?? '')) {
            $this->tpl->setCurrentBlock('title_link');
            $this->tpl->setVariable('TITLE_LINK', $a_set['title_link'] ?? '');
            $this->tpl->setVariable('TITLE_LINK_NAME', $a_set['title'] ?? '');
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('title_plain');
            $this->tpl->setVariable('TITLE', $a_set['title'] ?? '');
            $this->tpl->parseCurrentBlock();
        }
        if (strlen($a_set['desc'] ?? '')) {
            $this->tpl->setCurrentBlock('item_description');
            $this->tpl->setVariable('DESC', $a_set['desc'] ?? '');
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set['failure'] ?? false) {
            $this->tpl->setCurrentBlock('alert');
            $this->tpl->setVariable('IMG_ALERT', ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable('ALT_ALERT', $this->lng->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", $this->lng->txt($a_set['failure'] ?? ''));
            $this->tpl->parseCurrentBlock();
        }

        $error_post_item = (array) ($this->http->request()->getParsedBody()['item'] ?? []);

        // active
        $this->tpl->setVariable('NAME_ACTIVE', 'item[' . $a_set['ref_id'] . '][active]');
        if ($this->getFailureStatus()) {
            $active = (bool) ($error_post_item[$a_set['ref_id']]['active'] ?? false);
            $this->tpl->setVariable(
                'CHECKED_ACTIVE',
                $active ? 'checked="checked"' : ''
            );
        } else {
            $this->tpl->setVariable(
                'CHECKED_ACTIVE',
                ($a_set['item']['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) ? 'checked="checked"' : ''
            );
        }

        // start
        if ($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE) {
            $dt_input = new ilDateTimeInputGUI('', 'item[' . $a_set['ref_id'] . '][sug_start]');
            $dt_input->setDate(new ilDate($a_set['item']['suggestion_start'], IL_CAL_UNIX));
            if ($this->getFailureStatus()) {
                $start = (string) ($error_post_item[$a_set['ref_id']]['sug_start'] ?? '');
                $dt_input->setDate(new ilDate($start, IL_CAL_DATE));
            }

            $this->tpl->setVariable('start_abs');
            $this->tpl->setVariable('SUG_START', $dt_input->render());
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('start_rel');
            $this->tpl->setVariable('START_REL_VAL', (int) $a_set['item']['suggestion_start_rel']);
            if ($this->getFailureStatus()) {
                $start = (string) ($error_post_item[$a_set['ref_id']]['sug_start_rel'] ?? '');
                $this->tpl->setVariable('START_REL_VAL', $start);
            } else {
                $this->tpl->setVariable('START_REL_VAL', (int) $a_set['item']['suggestion_start_rel']);
            }
            $this->tpl->setVariable('START_REL_NAME', 'item[' . $a_set['ref_id'] . '][sug_start_rel]');
            $this->tpl->parseCurrentBlock();
        }

        if ($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_RELATIVE) {
            if ($this->getFailureStatus()) {
                $duration = (string) ($error_post_item[$a_set['ref_id']]['duration_a'] ?? '');
                $this->tpl->setVariable('VAL_DURATION_A', $duration);
            } else {
                $duration = $a_set['item']['suggestion_end_rel'] - $a_set['item']['suggestion_start_rel'];
                $this->tpl->setVariable('VAL_DURATION_A', (int) $duration);
            }
            $this->tpl->setVariable('NAME_DURATION_A', 'item[' . $a_set['ref_id'] . '][duration_a]');
        } else {
            $dt_end = new ilDateTimeInputGUI('', 'item[' . $a_set['ref_id'] . '][sug_end]');
            $dt_end->setDate(new ilDate($a_set['item']['suggestion_end'], IL_CAL_UNIX));
            if ($this->getFailureStatus()) {
                $end = (string) ($error_post_item[$a_set['ref_id']]['sug_end'] ?? '');
                $dt_end->setDate(new ilDate($end, IL_CAL_DATE));
            }

            $this->tpl->setVariable('end_abs');
            $this->tpl->setVariable('SUG_END', $dt_end->render());
            $this->tpl->parseCurrentBlock();
        }

        // changeable
        $this->tpl->setVariable('NAME_CHANGE', 'item[' . $a_set['ref_id'] . '][change]');
        $this->tpl->setVariable('CHECKED_CHANGE', $a_set['item']['changeable'] ? 'checked="checked"' : '');
        if ($this->getFailureStatus()) {
            $change = (bool) ($error_post_item[$a_set['ref_id']]['change'] ?? false);
            $this->tpl->setVariable(
                'CHECKED_CHANGE',
                $change ? 'checked="checked"' : ''
            );
        } else {
            $this->tpl->setVariable('CHECKED_CHANGE', $a_set['item']['changeable'] ? 'checked="checked"' : '');
        }
    }

    public function parse(array $a_item_data, array $a_failed_update = array()) : void
    {
        $rows = array();
        foreach ($a_item_data as $item) {
            $current_row = array();

            // no item groups
            if ($item['type'] == 'itgr') {
                continue;
            }
            $current_row['ref_id'] = $item['ref_id'];
            $current_row = $this->parseTitle($current_row, $item);

            // dubios error handling
            if (array_key_exists($item['ref_id'], $a_failed_update)) {
                $current_row['failed'] = true;
                $current_row['failure'] = $a_failed_update[$item['ref_id']];
            }
            $current_row['item'] = $item;

            $rows[] = $current_row;
        }
        $this->setData($rows);
    }

    protected function parseTitle(array $current_row, array $item) : array
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
