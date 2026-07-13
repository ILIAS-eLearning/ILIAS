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

use ILIAS\Test\Access\AccessFileUploadAnswer;
use ILIAS\Test\Access\AccessFileUploadPreview;
use ILIAS\Test\Access\AccessQuestionImage;
use ILIAS\Test\Access\SimpleAccess;
use ILIAS\Test\Access\Readable;
use ILIAS\Test\Results\Data\Repository;
use ILIAS\Test\Settings\ScoreReporting\ScoreReportingTypes;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;
use ILIAS\Test\TestDIC;

/**
 * Class ilObjTestAccess
 *
 * This class contains methods that check object specific conditions
 * for accessing test objects.
 *
 * @author    Helmut Schottmueller <helmut.schottmueller@mac.com>
 * @author    Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup components\ILIASTest
 */
class ilObjTestAccess extends ilObjectAccess implements ilConditionHandling
{
    private ilDBInterface $db;
    private ilObjUser $user;
    private ilLanguage $lng;
    private ilRbacSystem $rbac_system;
    private ilAccessHandler $access;

    private static ?ilCertificateObjectsForUserPreloader $certificate_preloader = null;
    private static array $settings_result_summaries_by_obj_id = [];

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->user = $DIC['ilUser'];
        $this->lng = $DIC['lng'];
        $this->rbac_system = $DIC['rbacsystem'];
        $this->access = $DIC['ilAccess'];
    }

    public function canBeDelivered(ilWACPath $ilWACPath): bool
    {
        $readable = new Readable($this->access);

        $can_it = $this->findMatch($ilWACPath->getPath(), [
            new AccessFileUploadAnswer($this->user, $this->db, $readable),
            new AccessQuestionImage($readable),
            new AccessFileUploadPreview($this->db, $this->access),
        ]);

        return !$can_it->isOk() || $can_it->value();
    }

    private function findMatch(string $path, array $array): Result
    {
        return array_reduce($array, fn(Result $result, SimpleAccess $access) => $result->except(
            fn() => $access->isPermitted($path)
        ), new Error('Not a known path.'));
    }

    /**
     * Checks wether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     *
     * Please do not check any preconditions handled by
     * ilConditionHandler here.
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        if (is_null($user_id)) {
            $user_id = $this->user->getId();
        }

        $is_admin = $this->rbac_system->checkAccessOfUser($user_id, 'write', $ref_id)
            || $this->rbac_system->checkAccessOfUser($user_id, 'score_anon', $ref_id);

        $is_online = !ilObject::lookupOfflineStatus($obj_id);

        if (!$is_admin && !$is_online) {
            return false;
        }

        switch ($permission) {
            case 'visible':
            case 'read':
                if (!ilObjTestAccess::lookupCreationComplete($obj_id) &&
                    !$is_admin) {
                    $this->access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $this->lng->txt('tst_warning_test_not_complete'));
                    return false;
                }
                break;
        }

        switch ($cmd) {
            case 'eval_stat':
                if (!ilObjTestAccess::lookupCreationComplete($obj_id)) {
                    $this->access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $this->lng->txt('tst_warning_test_not_complete'));
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * Get possible conditions operators
     */
    public static function getConditionOperators(): array
    {
        return [
            ilConditionHandler::OPERATOR_PASSED,
            ilConditionHandler::OPERATOR_FAILED,
            ilConditionHandler::OPERATOR_FINISHED,
            ilConditionHandler::OPERATOR_NOT_FINISHED,
            ilConditionHandler::OPERATOR_RESULT_RANGE_PERCENTAGE
        ];
    }


    /**
     * check condition
     *
     * this method is called by ilConditionHandler
     */
    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id): bool
    {
        /** @var Repository $test_result_repository */
        $test_result_repository = TestDIC::dic()['results.data.repository'];

        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                return $test_result_repository->isPassed($a_usr_id, $a_trigger_obj_id);

            case ilConditionHandler::OPERATOR_FAILED:
                return $test_result_repository->isFailed($a_usr_id, $a_trigger_obj_id);

            case ilConditionHandler::OPERATOR_FINISHED:
                return $test_result_repository->hasFinished($a_usr_id, $a_trigger_obj_id);

            case ilConditionHandler::OPERATOR_NOT_FINISHED:
                return !$test_result_repository->hasFinished($a_usr_id, $a_trigger_obj_id);

            case ilConditionHandler::OPERATOR_RESULT_RANGE_PERCENTAGE:
                $percentage_thresholds = self::deserializePercentageThresholds($a_value);
                if ($percentage_thresholds === false) {
                    return false;
                }
                return $test_result_repository->reachedPercentage(
                    $a_usr_id,
                    $a_trigger_obj_id,
                    $percentage_thresholds['min_percentage'],
                    $percentage_thresholds['max_percentage']
                );
            default:
                return true;
        }
    }

    public static function _getCommands(): array
    {
        global $DIC;
        $DIC->language()->loadLanguageModule('assessment');

        return [
            [
                'permission' => 'write',
                'cmd' => 'questionsTabGateway',
                'lang_var' => 'tst_edit_questions'
            ],
            [
                'permission' => 'write',
                'cmd' => 'ILIAS\Test\Settings\MainSettings\SettingsMainGUI::showForm',
                'lang_var' => 'settings'
            ],
            [
                'permission' => 'read',
                'cmd' => 'ILIAS\Test\Presentation\TestScreenGUI::testScreen',
                'lang_var' => 'tst_run',
                'default' => true
            ],
            [
                'permission' => 'score_anon',
                'cmd' => 'ILIAS\Test\Scoring\Manual\ConsecutiveScoringGUI::view',
                'lang_var' => 'manscoring'
            ],
        ];
    }

    //
    // object specific access related methods
    //

    public static function getBypassActivationCheckForPermissions(): array
    {
        return [
            'write',
            'score_anon'
        ];
    }

    private static function lookupCreationComplete(int $a_obj_id): bool
    {
        global $DIC;
        $db = $DIC->database();
        $result = $db->queryF(
            'SELECT complete FROM tst_tests WHERE obj_fi=%s',
            [ilDBConstants::T_INTEGER],
            [$a_obj_id]
        );
        return $result->numRows() > 0 && (bool) $db->fetchAssoc($result)['complete'];
    }

    /**
     * Returns the ILIAS test id for a given object id
     *
     * @param integer $object_id The object id
     * @return mixed The ILIAS test id or FALSE if the query was not successful
     * @access public
     */
    public static function _getTestIDFromObjectID(int $object_id): int|false
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $test_id = false;
        $result = $ilDB->queryF(
            'SELECT test_id FROM tst_tests WHERE obj_fi = %s',
            [ilDBConstants::T_INTEGER],
            [$object_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $test_id = $row['test_id'];
        }
        return $test_id;
    }

    /**
     * Get all tests using a question pool for random selection
     *
     * @param int     question pool id
     * @return    array    list if test obj ids
     * @access    public
     */
    public static function _getRandomTestsForQuestionPool(int $qpl_id): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT DISTINCT t.obj_fi' . PHP_EOL
            . 'FROM tst_tests t' . PHP_EOL
            . 'INNER JOIN tst_rnd_quest_set_qpls r' . PHP_EOL
            . 'ON t.test_id = r.test_fi' . PHP_EOL
            . 'WHERE r.pool_fi = %s' . PHP_EOL;

        $result = $ilDB->queryF($query, [ilDBConstants::T_INTEGER], [$qpl_id]);

        $tests = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $tests[] = $row['obj_fi'];
        }

        return $tests;
    }

    /**
     * Retrieves a participant name from active id
     *
     * @param integer $active_id Active ID of the participant
     * @return string The output name of the user
     * @access public
     */
    public static function _getParticipantData(int $active_id): string
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $result_active = $ilDB->queryF(
            'SELECT * FROM tst_active WHERE active_id = %s',
            [ilDBConstants::T_INTEGER],
            [$active_id]
        );
        $row_active = $ilDB->fetchAssoc($result_active);
        $importname = $row_active['importname'];

        if ($importname !== null
            && $importname !== '') {
            return $importname . ' (' . $lng->txt('imported') . ')';
        }

        if ($row_active['user_fi'] === ANONYMOUS_USER_ID) {
            return '';
        }

        $uname = ilObjUser::_lookupName($row_active['user_fi']);

        $result_test = $ilDB->queryF(
            'SELECT obj_fi FROM tst_tests WHERE test_id = %s',
            [ilDBConstants::T_INTEGER],
            [$row_active['test_fi']]
        );
        $row_test = $ilDB->fetchAssoc($result_test);
        $obj_id = $row_test['obj_fi'];

        $test_obj = new ilObjTest($obj_id, false);
        if ($test_obj->getAnonymity()) {
            return $lng->txt('anonymous');
        }

        if ($uname['firstname'] . $uname['lastname'] === '') {
            return $lng->txt('deleted_user');
        }

        return trim($uname['lastname'] . ', ' . $uname['firstname']);
    }

    /**
     * Get user id for active id
     *
     * @param int        active ID of the participant
     * @return    int        user id
     */
    public static function _getParticipantId(int $active_id): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            'SELECT user_fi FROM tst_active WHERE active_id = %s',
            [ilDBConstants::T_INTEGER],
            [$active_id]
        );
        $row = $ilDB->fetchAssoc($result);
        return $row['user_fi'];
    }

    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto(string $target): bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $t_arr = explode('_', $target);

        if ($t_arr[0] != 'tst' || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess('read', '', (int) $t_arr[1]) ||
            $ilAccess->checkAccess('visible', '', (int) $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * returns the objects's OFFline status
     *
     * Used in ListGUI and Learning Progress
     */
    public static function _isOffline(int $obj_id): bool
    {
        return ilObject::lookupOfflineStatus($obj_id);
    }


    public static function visibleUserResultExists(int $test_obj_id, int $user_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $test_obj = ilObjectFactory::getInstanceByObjId($test_obj_id, false);

        if (!($test_obj instanceof ilObjTest)) {
            return false;
        }

        $test_session_factory = new ilTestSessionFactory($test_obj, $ilDB, $ilUser);
        $test_session = $test_session_factory->getSessionByUserId($user_id);

        return $test_obj->canShowTestResults($test_session);
    }

    public static function _preloadData(array $obj_ids, array $ref_ids): void
    {
        global $DIC;
        if ((new ilCertificateActiveValidator())->validate()) {
            self::$certificate_preloader = new ilCertificateObjectsForUserPreloader(new ilUserCertificateRepository());
            self::$certificate_preloader->preLoad($DIC['ilUser']->getId(), $obj_ids);
            self::$settings_result_summaries_by_obj_id = TestDIC::dic()['settings.scoring.repository']
                ->getSettingsResultSummaryByObjIds($obj_ids);
        }
    }

    public function showCertificateFor(int $user_id, int $obj_id): bool
    {
        if (self::$certificate_preloader === null
            || !self::$certificate_preloader->isPreloaded($user_id, $obj_id)
            || !isset(self::$settings_result_summaries_by_obj_id[$obj_id])
            || self::$settings_result_summaries_by_obj_id[$obj_id]->getScoreReporting()
                === ScoreReportingTypes::SCORE_REPORTING_DISABLED) {
            return false;
        }

        $score_reporting = self::$settings_result_summaries_by_obj_id[$obj_id]->getScoreReporting();
        if ($score_reporting === ScoreReportingTypes::SCORE_REPORTING_IMMIDIATLY) {
            return true;
        }

        if ($score_reporting === ScoreReportingTypes::SCORE_REPORTING_DATE
            && self::$settings_result_summaries_by_obj_id[$obj_id]->getReportingDate() < new \DateTimeImmutable('now', new DateTimeZone('UTC'))) {
            return true;
        }

        return false;
    }

    /**
     * @return array{min_percentage: float, max_percentage: float}|false
     */
    private static function deserializePercentageThresholds(string $value): array|false
    {
        $value_arr = unserialize($value);

        if ($value_arr === false) {
            return false;
        }

        return [
            'min_percentage' => (float) ($value_arr['min_percentage'] ?? 0.0) / 100,
            'max_percentage' => (float) ($value_arr['max_percentage'] ?? 0.0) / 100
        ];
    }
}
