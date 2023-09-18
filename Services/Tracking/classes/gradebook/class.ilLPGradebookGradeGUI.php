<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/gradebook/class.ilLPGradebookGUI.php");


/**
 * @author JKN Inc. <itstaff@cpkn.ca>
 * @version $Id$
 *
 * @ingroup Services
 */

class ilLPGradebookGradeGUI extends ilLPGradebookGUI
{
    protected $lng;
    protected $tpl;
    protected $participants;
    protected $versions;
    protected $revision_id;
    protected $participants_data;
    protected $user_grade_data;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->lng = $DIC["lng"];
        $this->tpl = $DIC["tpl"];
    }

    /**
     * @return mixed
     */
    public function getUserGradeData()
    {
        return $this->user_grade_data;
    }

    /**
     * @param mixed $user_grade_data
     */
    public function setUserGradeData($user_grade_data)
    {
        $this->user_grade_data = $user_grade_data;
    }
    /**
     * @return mixed
     */
    public function getParticipantsData()
    {
        return $this->participants_data;
    }

    /**
     * @param mixed $participants_data
     */
    public function setParticipantsData($participants_data)
    {
        $this->participants_data = $participants_data;
    }

    /**
     * @return mixed
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param mixed $versions
     */
    public function setVersions($versions)
    {
        $this->versions = $versions;
    }

    /**
     * @return mixed
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param mixed $participants
     */
    public function setParticipants($participants)
    {
        $this->participants = $participants;
    }


    /**
     * @return mixed
     */
    public function getRevisionId()
    {
        return $this->revision_id;
    }

    /**
     * @param mixed $revision_id
     */
    public function setRevisionId($revision_id)
    {
        $this->revision_id = $revision_id;
    }

    private function buildParticipantsOptions()
    {
        $option_txt = '';
        foreach ($this->participants as $participant) {
            $option_txt .= '<option value="' . $participant['usr_id'] . '">' . $participant['full_name'] . ' ('.$participant['login'].')</option>';
        }
        return $option_txt;
    }

    /**
     *
     */
    public function view()
    {
        $my_tpl = new ilTemplate('tpl.lp_gradebook_grade.html', true, true, "Services/Tracking");

        $this->tpl->addJavascript('https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js', true, 1);

        // Custom
        $this->tpl->addCss('./Services/Tracking/css/ilGradebook.css');
        $this->tpl->addJavascript('./Services/Tracking/js/ilGradebookGrade.js');

        $my_tpl->setVariable("LPTYPE", $this->lng->txt('gradebook_lptype'));
        $my_tpl->setVariable("DEPTH", $this->lng->txt('gradebook_depth'));
        $my_tpl->setVariable("WEIGHT", $this->lng->txt('gradebook_weight') . ' %');
        $my_tpl->setVariable("ACTUAL", $this->lng->txt('gradebook_actual') . ' %');
        $my_tpl->setVariable("ADJUSTED", $this->lng->txt('gradebook_adjusted') . ' %');
        $my_tpl->setVariable("STATUS", $this->lng->txt('gradebook_status'));
        $my_tpl->setVariable("OBJECT", $this->lng->txt('gradebook_object'));
        $my_tpl->setVariable("TITLE", $this->lng->txt('gradebook_title'));
        $my_tpl->setVariable("GRADED_ON", $this->lng->txt('gradebook_graded_on'));
        $my_tpl->setVariable("GRADED_BY", $this->lng->txt('gradebook_graded_by'));
        $my_tpl->setVariable('LEGEND', $this->__getLegendHTML());

        $options = $this->buildParticipantsOptions();
        $revisions = $this->buildGradebookVersionsOptions();

        $my_tpl->setVariable("OVERALL_STATUS", $this->lng->txt('gradebook_overall_status') . ' : ');
        $my_tpl->setVariable("USER_OPTIONS", $options);
        $my_tpl->setVariable("GRADEBOOK_REVISIONS", $revisions);

        


        $this->tpl->setContent($my_tpl->get());
    }

    public function __getLegendHTML()
    {

        $tpl = new ilTemplate("tpl.lp_gradebook_legend.html", true, true, "Services/Tracking");
        $tpl->setVariable(
            "TXT_MARK_GRADEBOOK",
            $this->lng->txt("gradebook_mark_gradebook")
        );
        $tpl->setVariable(
            "TXT_MARK_CHILDREN",
            $this->lng->txt("gradebook_mark_children")
        );
        $tpl->setVariable(
            "TXT_MARK_AUTOMATED",
            $this->lng->txt("gradebook_mark_automated")
        );

        include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
        $panel = ilPanelGUI::getInstance();
        $panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
        $panel->setBody($tpl->get());

        return $panel->getHTML();
    }

    /**
     *
     */
    function courseParticipants()
    {
        $my_tpl = new ilTemplate('tpl.lp_gradebook_course_participants.html', true, true, "Services/Tracking");

        // Custom
        $this->tpl->addCss('./Services/Tracking/css/ilGradebook.css');

        $my_tpl->setVariable("HEADER", $this->lng->txt('gradebook_students'));

        $my_tpl->setVariable("ALL_GRADES", $this->lng->txt('gradebook_all_grades'));



        $my_tpl->setVariable("STUDENT_NAME", $this->lng->txt('gradebook_student'));
        $my_tpl->setVariable("LOGIN", $this->lng->txt('login'));
        $my_tpl->setVariable("REVISION", $this->lng->txt('gradebook_revision'));
        $my_tpl->setVariable("OVERALL", $this->lng->txt('gradebook_overall_grade'));
        $my_tpl->setVariable("ADJUSTED", $this->lng->txt('gradebook_adjusted_grade'));
        $my_tpl->setVariable("PROGRESS", $this->lng->txt('gradebook_progress'));
        $my_tpl->setVariable("STATUS", $this->lng->txt('gradebook_status'));

        $tableHTML = '';

        foreach ($this->participants_data['user_grades'] as $object) {
            $tableHTML .= '<tr>';
            $tableHTML .= '<td>' . $object['student_name'] . '</td>';
            $tableHTML .= '<td>' . $object['login'] . '</td>';
            $tableHTML .= '<td>' . $object['revision'] . '</td>';
            $tableHTML .= '<td>' . $object['overall_grade'] . '%</td>';
            $tableHTML .= '<td>' . $object['adjusted_grade'] . '%</td>';
            $tableHTML .= '<td>' . $object['progress'] . '%</td>';
            $tableHTML .= '<td><img title="' . ilLearningProgressBaseGUI::_getStatusText($object['status']) . '" alt="' . ilLearningProgressBaseGUI::_getStatusText($object['status']) . '" src="' . ilLearningProgressBaseGUI::_getImagePathForStatus($object['status']) . '"></td>';
            $tableHTML .= '</tr>';
        }

        $my_tpl->setVariable("STUDENTS", $tableHTML);
        $this->tpl->setContent($my_tpl->get());
    }


    public function getStudentViewHTML()
    {
        $my_tpl = new ilTemplate('tpl.lp_gradebook_student_view.html', true, true, "Services/Tracking");

        // Custom
        $this->tpl->addCss('./Services/Tracking/css/ilGradebook.css');

        $my_tpl->setVariable("OVERALL_GRADE", $this->lng->txt('gradebook_overall_grade') . ' ' .
            $this->user_grade_data['overall'][0]['overall_grade'] . '%');

        $my_tpl->setVariable("ADJUSTED_GRADE", $this->lng->txt('gradebook_adjusted_grade') . ' ' .
            $this->user_grade_data['overall'][0]['adjusted_grade'] . '%');

         $my_tpl->setVariable("PASSING_GRADE", $this->lng->txt('gradebook_passing_grade') . ' ' .
            $this->user_grade_data['passing_grade'] . '%'); 

        $my_tpl->setVariable("OVERALL_PROGRESS", $this->lng->txt('gradebook_overall_progress') . ' ' .
            $this->user_grade_data['overall'][0]['progress'] . '%');
        $my_tpl->setVariable("LPTYPE", $this->lng->txt('gradebook_lptype'));
        $my_tpl->setVariable("DEPTH", $this->lng->txt('gradebook_depth'));
        $my_tpl->setVariable("WEIGHT", $this->lng->txt('gradebook_weight'));
        $my_tpl->setVariable("ACTUAL", $this->lng->txt('gradebook_actual'));
        $my_tpl->setVariable("ADJUSTED", $this->lng->txt('gradebook_adjusted'));
        $my_tpl->setVariable("STATUS", $this->lng->txt('gradebook_status'));
        $my_tpl->setVariable("OBJECT", $this->lng->txt('gradebook_object'));
        $my_tpl->setVariable("TITLE", $this->lng->txt('gradebook_title'));
        $my_tpl->setVariable("GRADED_ON", $this->lng->txt('gradebook_graded_on'));
        $my_tpl->setVariable("GRADED_BY", $this->lng->txt('gradebook_graded_by'));

        $tableHTML = '';

        foreach ($this->user_grade_data['grade_objects']['object_data'] as $data) {
            if (!$data['is_gradeable']) {
                $span = '<span title="Group grade is determined by children" class="obj-learning-progress glyphicon glyphicon-lock" aria-hidden="true"></span></td>';
            } else {
                if ($data['lp_type'] == 0) {
                    $span = '<span  title="Automated learning progress" class="obj-learning-progress glyphicon glyphicon-ok" aria-hidden="true"></span>';
                } else {
                    $span = '<span  title="Learning progress is either disabled or not available" class="obj-learning-progress glyphicon glyphicon-pencil" aria-hidden="true"></span>';
                }
            }
            $tableHTML .= '<tr>';
            $tableHTML .= '<td>' . $span . '</td>';
            $tableHTML .= '<td>' . $data['placement_depth'] . '</td>';
            $tableHTML .= '<td>' . $data['weight'] . '%</td>';
            $tableHTML .= '<td>' . $data['actual'] . '%</td>'; 
            $tableHTML .= '<td>' . $data['adjusted'] . '%</td>';
            $tableHTML .= '<td><img title="' . ilLearningProgressBaseGUI::_getStatusText($data['status']) . '" alt="' . ilLearningProgressBaseGUI::_getStatusText($data['status']) . '" src="' . ilLearningProgressBaseGUI::_getImagePathForStatus($data['status']) . '"></td>';
            $tableHTML .= '<td> <img alt="' . $data['type_Alt'] . '" title="' . $data['type_Alt'] . '" src="./templates/default/images/icon_' . $data['type'] . '.svg" class="ilListItemIcon"></td>';
            $tableHTML .= '<td>' . $data['title'] . '</td>';
            $tableHTML .= '<td>' . $data['graded_on'] . '</td>';
            $tableHTML .= '<td>' . $data['graded_by'] . '</td>';
            $tableHTML .= '</tr>';
        }

        $my_tpl->setVariable("STUDENT", $tableHTML);

        return $my_tpl->get();
    }
}
