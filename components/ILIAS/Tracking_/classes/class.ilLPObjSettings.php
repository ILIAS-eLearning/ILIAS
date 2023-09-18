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

/**
 * Class ilLPObjSettings
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-tracking
 */
class ilLPObjSettings
{
    protected int $obj_id;
    protected string $obj_type;
    protected int $obj_mode;
    protected int $visits = self::LP_DEFAULT_VISITS;

    protected bool $is_stored = false;

    public const LP_MODE_DEACTIVATED = 0;
    public const LP_MODE_TLT = 1;
    public const LP_MODE_VISITS = 2;
    public const LP_MODE_MANUAL = 3;
    public const LP_MODE_OBJECTIVES = 4;
    public const LP_MODE_COLLECTION = 5;
    public const LP_MODE_SCORM = 6;
    public const LP_MODE_TEST_FINISHED = 7;
    public const LP_MODE_TEST_PASSED = 8;
    public const LP_MODE_EXERCISE_RETURNED = 9;
    public const LP_MODE_EVENT = 10;
    public const LP_MODE_MANUAL_BY_TUTOR = 11;
    public const LP_MODE_SCORM_PACKAGE = 12;
    public const LP_MODE_UNDEFINED = 13;
    public const LP_MODE_PLUGIN = 14;
    public const LP_MODE_COLLECTION_TLT = 15;
    public const LP_MODE_COLLECTION_MANUAL = 16;
    public const LP_MODE_QUESTIONS = 17;
    public const LP_MODE_SURVEY_FINISHED = 18;
    public const LP_MODE_VISITED_PAGES = 19;
    public const LP_MODE_CONTENT_VISITED = 20;
    public const LP_MODE_COLLECTION_MOBS = 21;
    public const LP_MODE_STUDY_PROGRAMME = 22;
    public const LP_MODE_INDIVIDUAL_ASSESSMENT = 23;
    public const LP_MODE_CMIX_COMPLETED = 24;
    public const LP_MODE_CMIX_COMPL_WITH_FAILED = 25;
    public const LP_MODE_CMIX_PASSED = 26;
    public const LP_MODE_CMIX_PASSED_WITH_FAILED = 27;
    public const LP_MODE_CMIX_COMPLETED_OR_PASSED = 28;
    public const LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED = 29;
    public const LP_MODE_LTI_OUTCOME = 31;
    public const LP_MODE_COURSE_REFERENCE = 32;
    public const LP_MODE_CONTRIBUTION_TO_DISCUSSION = 33;

    public const LP_DEFAULT_VISITS = 30;

    protected static array $map = array(

        self::LP_MODE_DEACTIVATED => array('ilLPStatus',
                                           'trac_mode_deactivated',
                                           'trac_mode_deactivated_info_new'
        )

        ,
        self::LP_MODE_TLT => array('ilLPStatusTypicalLearningTime',
                                   'trac_mode_tlt',
                                   'trac_mode_tlt_info'
        ) // info has dynamic part!

        ,
        self::LP_MODE_VISITS => array('ilLPStatusVisits',
                                      'trac_mode_visits',
                                      'trac_mode_visits_info'
        )

        ,
        self::LP_MODE_MANUAL => array('ilLPStatusManual',
                                      'trac_mode_manual',
                                      'trac_mode_manual_info'
        )

        ,
        self::LP_MODE_OBJECTIVES => array('ilLPStatusObjectives',
                                          'trac_mode_objectives',
                                          'trac_mode_objectives_info'
        )

        ,
        self::LP_MODE_COLLECTION => array('ilLPStatusCollection',
                                          'trac_mode_collection',
                                          'trac_mode_collection_info'
        )

        ,
        self::LP_MODE_SCORM => array('ilLPStatusSCORM',
                                     'trac_mode_scorm',
                                     'trac_mode_scorm_info'
        )

        ,
        self::LP_MODE_TEST_FINISHED => array('ilLPStatusTestFinished',
                                             'trac_mode_test_finished',
                                             'trac_mode_test_finished_info'
        )

        ,
        self::LP_MODE_TEST_PASSED => array('ilLPStatusTestPassed',
                                           'trac_mode_test_passed',
                                           'trac_mode_test_passed_info'
        )

        ,
        self::LP_MODE_EXERCISE_RETURNED => array('ilLPStatusExerciseReturned',
                                                 'trac_mode_exercise_returned',
                                                 'trac_mode_exercise_returned_info'
        )

        ,
        self::LP_MODE_EVENT => array('ilLPStatusEvent',
                                     'trac_mode_event',
                                     'trac_mode_event_info'
        )

        ,
        self::LP_MODE_MANUAL_BY_TUTOR => array('ilLPStatusManualByTutor',
                                               'trac_mode_manual_by_tutor',
                                               'trac_mode_manual_by_tutor_info'
        )

        ,
        self::LP_MODE_SCORM_PACKAGE => array('ilLPStatusSCORMPackage',
                                             'trac_mode_scorm_package',
                                             'trac_mode_scorm_package_info'
        )

        ,
        self::LP_MODE_UNDEFINED => null

        ,
        self::LP_MODE_PLUGIN => array('ilLPStatusPlugin',
                                      'trac_mode_plugin',
                                      ''
        ) // no settings screen, so no info needed

        ,
        self::LP_MODE_COLLECTION_TLT => array('ilLPStatusCollectionTLT',
                                              'trac_mode_collection_tlt',
                                              'trac_mode_collection_tlt_info'
        )

        ,
        self::LP_MODE_COLLECTION_MANUAL => array('ilLPStatusCollectionManual',
                                                 'trac_mode_collection_manual',
                                                 'trac_mode_collection_manual_info'
        )

        ,
        self::LP_MODE_QUESTIONS => array('ilLPStatusQuestions',
                                         'trac_mode_questions',
                                         'trac_mode_questions_info'
        )

        ,
        self::LP_MODE_SURVEY_FINISHED => array('ilLPStatusSurveyFinished',
                                               'trac_mode_survey_finished',
                                               'trac_mode_survey_finished_info'
        )

        ,
        self::LP_MODE_VISITED_PAGES => array('ilLPStatusVisitedPages',
                                             'trac_mode_visited_pages',
                                             'trac_mode_visited_pages_info'
        )

        ,
        self::LP_MODE_CONTENT_VISITED => array('ilLPStatusContentVisited',
                                               'trac_mode_content_visited',
                                               'trac_mode_content_visited_info'
        )

        ,
        self::LP_MODE_COLLECTION_MOBS => array('ilLPStatusCollectionMobs',
                                               'trac_mode_collection_mobs',
                                               'trac_mode_collection_mobs_info'
        )

        ,
        self::LP_MODE_STUDY_PROGRAMME => array('ilLPStatusStudyProgramme',
                                               'trac_mode_study_programme',
                                               ''
        )

        ,
        self::LP_MODE_INDIVIDUAL_ASSESSMENT => array('ilLPStatusIndividualAssessment',
                                                     'trac_mode_individual_assessment',
                                                     'trac_mode_individual_assessment_info'
        )

        ,
        self::LP_MODE_CMIX_COMPLETED => array(ilLPStatusCmiXapiCompleted::class,
                                              'trac_mode_cmix_completed',
                                              'trac_mode_cmix_completed_info'
        )

        ,
        self::LP_MODE_CMIX_COMPL_WITH_FAILED => array(ilLPStatusCmiXapiCompletedWithFailed::class,
                                                      'trac_mode_cmix_compl_with_failed',
                                                      'trac_mode_cmix_compl_with_failed_info'
        )

        ,
        self::LP_MODE_CMIX_PASSED => array(ilLPStatusCmiXapiPassed::class,
                                           'trac_mode_cmix_passed',
                                           'trac_mode_cmix_passed_info'
        )

        ,
        self::LP_MODE_CMIX_PASSED_WITH_FAILED => array(ilLPStatusCmiXapiPassedWithFailed::class,
                                                       'trac_mode_cmix_passed_with_failed',
                                                       'trac_mode_cmix_passed_with_failed_info'
        )

        ,
        self::LP_MODE_CMIX_COMPLETED_OR_PASSED => array(ilLPStatusCmiXapiCompletedOrPassed::class,
                                                        'trac_mode_cmix_completed_or_passed',
                                                        'trac_mode_cmix_completed_or_passed_info'
        )

        ,
        self::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED => array(ilLPStatusCmiXapiCompletedOrPassedWithFailed::class,
                                                                'trac_mode_cmix_compl_or_passed_with_failed',
                                                                'trac_mode_cmix_compl_or_passed_with_failed_info'
        )

        ,
        self::LP_MODE_LTI_OUTCOME => array(ilLPStatusLtiOutcome::class,
                                           'trac_mode_lti_outcome',
                                           'trac_mode_lti_outcome_info'
        )

        ,
        self::LP_MODE_COURSE_REFERENCE => [
            'ilLPStatusCourseReference',
            'trac_mode_course_reference',
            'trac_mode_course_reference_info'
        ],

        self::LP_MODE_CONTRIBUTION_TO_DISCUSSION => [
            ilLPStatusContributionToDiscussion::class,
            'trac_mode_contribution_to_discussion',
            'trac_mode_contribution_to_discussion_info'
        ],
    );

    protected ilDBInterface $db;
    protected ilObjectDataCache $objectDataCache;

    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->objectDataCache = $DIC['ilObjDataCache'];

        $this->obj_id = $a_obj_id;

        if (!$this->read()) {
            $this->obj_type = $this->objectDataCache->lookupType($this->obj_id);

            $olp = ilObjectLP::getInstance($this->obj_id);
            $this->obj_mode = $olp->getDefaultMode();
        }
    }

    /**
     * Clone settings
     * @access public
     * @param int new obj id
     */
    public function cloneSettings(int $a_new_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "INSERT INTO ut_lp_settings (obj_id,obj_type,u_mode,visits) " .
            "VALUES( " .
            $this->db->quote($a_new_obj_id, 'integer') . ", " .
            $this->db->quote($this->getObjType(), 'text') . ", " .
            $this->db->quote($this->getMode(), 'integer') . ", " .
            $this->db->quote($this->getVisits(), 'integer') .
            ")";
        $res = $this->db->manipulate($query);
        return true;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function setVisits(int $a_visits): void
    {
        $this->visits = $a_visits;
    }

    public function setMode(int $a_mode): void
    {
        $this->obj_mode = $a_mode;
    }

    public function getMode(): int
    {
        return $this->obj_mode;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function read(): bool
    {
        $res = $this->db->query(
            "SELECT * FROM ut_lp_settings WHERE obj_id = " .
            $this->db->quote($this->obj_id, 'integer')
        );
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->is_stored = true;
            $this->obj_type = (string) $row->obj_type;
            $this->obj_mode = (int) $row->u_mode;
            $this->visits = (int) $row->visits;
            return true;
        }
        return false;
    }

    public function update(bool $a_refresh_lp = true): bool
    {
        if (!$this->is_stored) {
            return $this->insert();
        }
        $query = "UPDATE ut_lp_settings SET u_mode = " . $this->db->quote(
            $this->getMode(),
            'integer'
        ) . ", " .
            "visits = " . $this->db->quote(
                $this->getVisits(),
                'integer'
            ) . " " .
            "WHERE obj_id = " . $this->db->quote($this->getObjId(), 'integer');
        $res = $this->db->manipulate($query);
        $this->read();

        if ($a_refresh_lp) {
            $this->doLPRefresh();
        }
        return true;
    }

    public function insert(): bool
    {
        $query = "INSERT INTO ut_lp_settings (obj_id,obj_type,u_mode,visits) " .
            "VALUES(" .
            $this->db->quote($this->getObjId(), 'integer') . ", " .
            $this->db->quote($this->getObjType(), 'text') . ", " .
            $this->db->quote($this->getMode(), 'integer') . ", " .
            $this->db->quote($this->getVisits(), 'integer') .  // #12482
            ")";
        $res = $this->db->manipulate($query);
        $this->read();
        $this->doLPRefresh();
        return true;
    }

    protected function doLPRefresh(): void
    {
        // refresh learning progress
        ilLPStatusWrapper::_refreshStatus($this->getObjId());
    }

    public static function _delete(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM ut_lp_settings WHERE obj_id = " . $ilDB->quote(
            $a_obj_id,
            'integer'
        );
        $res = $ilDB->manipulate($query);
        return true;
    }

    public static function _lookupVisits(int $a_obj_id): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT visits FROM ut_lp_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->visits;
        }
        return self::LP_DEFAULT_VISITS;
    }

    public static function _lookupDBModeForObjects(array $a_obj_ids): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        // this does NOT handle default mode!
        $res = array();
        $query = "SELECT obj_id, u_mode FROM ut_lp_settings" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer");
        $set = $ilDB->query($query);
        while ($row = $set->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $res[(int) $row->obj_id] = (int) $row->u_mode;
        }
        return $res;
    }

    public static function _lookupDBMode(int $a_obj_id): ?int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        // this does NOT handle default mode!
        $query = "SELECT u_mode FROM ut_lp_settings" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->u_mode;
        }
        return null;
    }

    public static function _mode2Text(int $a_mode): string
    {
        global $DIC;

        $lng = $DIC->language();
        if (array_key_exists($a_mode, self::$map) &&
            is_array(self::$map[$a_mode])) {
            return $lng->txt(self::$map[$a_mode][1]);
        }
        return '';
    }

    public static function _mode2InfoText(int $a_mode): string
    {
        global $DIC;

        $lng = $DIC->language();
        if (array_key_exists($a_mode, self::$map) &&
            is_array(self::$map[$a_mode])) {
            $info = $lng->txt(self::$map[$a_mode][2]);
            if ($a_mode == self::LP_MODE_TLT) {
                // dynamic content
                $info = sprintf($info, ilObjUserTracking::_getValidTimeSpan());
            }
            return $info;
        }
        return '';
    }

    public static function getClassMap(): array
    {
        $res = array();
        foreach (self::$map as $mode => $item) {
            if ($item) {
                $res[$mode] = $item[0];
            }
        }
        return $res;
    }

    public static function _deleteByObjId(int $a_obj_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        // we are only removing settings for now
        // invalid ut_lp_collections-entries are filtered
        // ut_lp_marks is deemed private user data

        $ilDB->manipulate(
            "DELETE FROM ut_lp_settings" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
    }
}
