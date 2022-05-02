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
 
/**
 * Class ilLOmemberTestResultTableGUI
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLOMemberTestResultTableGUI extends ilTable2GUI
{
    private ilLOSettings $settings;
    private ilObject $parent_container;

    private int $current_user = 0;

    public function __construct(object $a_parent_obj_gui, ilObject $a_parent_obj, string $a_parent_cmd)
    {
        $this->parent_container = $a_parent_obj;
        $this->setId('lomemtstres_' . $a_parent_obj->getId());
        parent::__construct($a_parent_obj_gui, $a_parent_cmd);
        $this->settings = ilLOSettings::getInstanceByObjId($a_parent_obj->getId());
    }

    public function getParentContainer() : ilObject
    {
        return $this->parent_container;
    }

    public function getSettings() : ilLOSettings
    {
        return $this->settings;
    }

    public function setUserId(int $a_id) : void
    {
        $this->current_user = $a_id;
    }

    public function getUserId() : int
    {
        return $this->current_user;
    }

    public function init() : void
    {
        $name = ilObjUser::_lookupName($this->getUserId());

        if (strlen($name['firstname']) && strlen($name['lastname'])) {
            $name_string = $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']';
        } elseif (strlen($name['lastname']) !== 0) {
            $name_string = $name['lastname'] . ' [' . $name['login'] . ']';
        } else {
            $name_string = $name['login'];
        }
        $this->setTitle($this->lng->txt('crs_loc_test_results_of') . ' ' . $name_string);
        $this->addColumn($this->lng->txt('crs_objectives'), 'title', '50%');
        if ($this->getSettings()->worksWithInitialTest()) {
            $this->addColumn($this->lng->txt('crs_loc_itest_info'), 'it', '25%');
            $this->addColumn($this->lng->txt('crs_loc_qtest_info'), 'qt', '25%');
        } else {
            $this->addColumn($this->lng->txt('crs_loc_qtest_info'), 'qt', '25%');
        }
        $this->setRowTemplate('tpl.crs_objectives_usr_result_row.html', 'Modules/Course');
        $this->disable('sort');
        $this->disable('num_info');
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if ($this->getSettings()->worksWithInitialTest()) {
            if ($a_set['has_result_it']) {
                $this->tpl->setCurrentBlock('it_has_result');
                $this->tpl->setVariable('IT_LINK', $a_set['link_it']);
                $this->tpl->setVariable('IT_VAL', $a_set['res_it'] . '%');
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setVariable('IT_NO_RES', '-');
            }
        }

        if ($a_set['has_result_qt']) {
            $this->tpl->setCurrentBlock('qt_has_result');
            $this->tpl->setVariable('QT_LINK', $a_set['link_qt']);
            $this->tpl->setVariable('QT_VAL', $a_set['res_qt'] . '%');
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setVariable('QT_NO_RES', '-');
        }
    }

    public function parse() : void
    {
        $objective_ids = ilCourseObjective::_getObjectiveIds($this->getParentContainer()->getId(), true);
        $tbl_data = [];
        foreach ($objective_ids as $objective_id) {
            $objective = array();
            $objective['id'] = $objective_id;
            $objective['title'] = ilCourseObjective::lookupObjectiveTitle($objective_id);

            if ($this->getSettings()->worksWithInitialTest()) {
                $results_it = ilLOUserResults::lookupResult(
                    $this->getParentContainer()->getId(),
                    $this->getUserId(),
                    $objective_id,
                    ilLOSettings::TYPE_TEST_INITIAL
                );
                $objective['tries_it'] = $results_it['tries'];
                $objective['res_it'] = $results_it['result_perc'];
                $objective['link_it'] = $this->createTestResultLink(ilLOSettings::TYPE_TEST_INITIAL, $objective_id);
                $objective['has_result_it'] = (bool) $results_it['has_result'];
            }
            $results_qt = ilLOUserResults::lookupResult(
                $this->getParentContainer()->getId(),
                $this->getUserId(),
                $objective_id,
                ilLOSettings::TYPE_TEST_QUALIFIED
            );
            $objective['tries_qt'] = $results_qt['tries'];
            $objective['res_qt'] = $results_qt['result_perc'];
            $objective['link_qt'] = $this->createTestResultLink(ilLOSettings::TYPE_TEST_QUALIFIED, $objective_id);
            $objective['has_result_qt'] = (bool) $results_qt['has_result'];

            $tbl_data[] = $objective;
        }
        $this->setData($tbl_data);
    }

    protected function createTestResultLink(int $a_type, int $a_objective_id) : string
    {
        $assignments = ilLOTestAssignments::getInstance($this->getParentContainer()->getId());

        $test_ref_id = $assignments->getTestByObjective($a_objective_id, $a_type);
        if ($test_ref_id === 0) {
            return '';
        }
        return ilLOUtils::getTestResultLinkForUser($test_ref_id, $this->getUserId());
    }
}
