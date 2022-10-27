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

/**
 * Class ilCronFinishUnfinishedTestPasses
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilCronFinishUnfinishedTestPasses extends ilCronJob
{
    /**
     * @var ilLogger|ilComponentLogger
     */
    protected $log;

    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected ilObjectDataCache $obj_data_cache;
    protected int $now;
    protected array $unfinished_passes;
    protected array $test_ids;
    protected array $test_ending_times;
    protected ilTestProcessLockerFactory $processLockerFactory;

    public function __construct()
    {
        global $DIC;

        $this->log = ilLoggerFactory::getLogger('tst');
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('assessment');
        $this->db = $DIC->database();
        $this->obj_data_cache = $DIC['ilObjDataCache'];
        $this->now = time();
        $this->unfinished_passes = array();
        $this->test_ids = array();
        $this->test_ending_times = array();

        require_once 'Modules/Test/classes/class.ilTestProcessLockerFactory.php';
        $this->processLockerFactory = new ilTestProcessLockerFactory(
            new ilSetting('assessment'),
            $this->db
        );
    }

    public function getId(): string
    {
        return 'finish_unfinished_passes';
    }

    public function getTitle(): string
    {
        global $DIC;
        $lng = $DIC['lng'];

        return $lng->txt("finish_unfinished_passes");
    }

    public function getDescription(): string
    {
        global $DIC;
        $lng = $DIC['lng'];

        return $lng->txt("finish_unfinished_passes_desc");
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue(): int
    {
        return 1;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function run(): ilCronJobResult
    {
        $this->log->info('start inf cronjob...');

        $result = new ilCronJobResult();

        $this->gatherUsersWithUnfinishedPasses();
        if (count($this->unfinished_passes) > 0) {
            $this->log->info('found ' . count($this->unfinished_passes) . ' unfinished passes starting analyses.');
            $this->getTestsFinishAndProcessingTime();
            $this->processPasses();
        } else {
            $this->log->info('No unfinished passes found.');
        }

        $result->setStatus(ilCronJobResult::STATUS_OK);

        $this->log->info(' ...finishing cronjob.');

        return $result;
    }

    protected function gatherUsersWithUnfinishedPasses(): void
    {
        $query = "SELECT	tst_active.active_id,
						tst_active.tries,
						tst_active.user_fi usr_id,
						tst_active.test_fi test_fi,
						usr_data.login,
						usr_data.lastname,
						usr_data.firstname,
						tst_active.submitted test_finished,
						usr_data.matriculation,
						usr_data.active,
						tst_active.lastindex,
						tst_active.last_started_pass last_started
				FROM tst_active
				LEFT JOIN usr_data
				ON tst_active.user_fi = usr_data.usr_id
				WHERE IFNULL(tst_active.last_finished_pass, -1) <> tst_active.last_started_pass 
			";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $this->unfinished_passes[] = $row;
            $this->test_ids[] = $row['test_fi'];
        }
    }

    protected function getTestsFinishAndProcessingTime(): void
    {
        $query = 'SELECT test_id, obj_fi, ending_time, ending_time_enabled, processing_time, enable_processing_time FROM tst_tests WHERE ' .
                    $this->db->in('test_id', $this->test_ids, false, 'integer');
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $this->test_ending_times[$row['test_id']] = $row;
        }
        $this->log->info('Gathered data for ' . count($this->test_ids) . ' test id(s) => (' . implode(',', $this->test_ids) . ')');
    }

    protected function processPasses(): void
    {
        $now = time();
        foreach ($this->unfinished_passes as $key => $data) {
            $test_id = $data['test_fi'];
            $can_not_be_finished = true;
            if (array_key_exists($test_id, $this->test_ending_times)) {
                if ($this->test_ending_times[$test_id]['ending_time_enabled'] == 1) {
                    $this->log->info('Test (' . $test_id . ') has ending time (' . $this->test_ending_times[$test_id]['ending_time'] . ')');
                    $ending_time = $this->test_ending_times[$test_id]['ending_time'];
                    if ($ending_time < $now) {
                        $this->finishPassForUser($data['active_id'], $this->test_ending_times[$test_id]['obj_fi']);
                        $can_not_be_finished = false;
                    } else {
                        $this->log->info('Test (' . $test_id . ') ending time (' . $this->test_ending_times[$test_id]['ending_time'] . ') > now (' . $now . ') is not reached.');
                    }
                } else {
                    $this->log->info('Test (' . $test_id . ') has no ending time.');
                }
                if ($this->test_ending_times[$test_id]['enable_processing_time'] == 1) {
                    $this->log->info('Test (' . $test_id . ') has processing time (' . $this->test_ending_times[$test_id]['processing_time'] . ')');
                    $obj_id = $this->test_ending_times[$test_id]['obj_fi'];
                    $test_obj = new ilObjTest($obj_id, false);
                    $startingTime = $test_obj->getStartingTimeOfUser($data['active_id'], $data['last_started']);
                    $max_processing_time = $test_obj->isMaxProcessingTimeReached($startingTime, $data['active_id']);
                    if ($max_processing_time) {
                        $this->log->info('Max Processing time reached for user id (' . $data['usr_id'] . ') so test with active id (' . $data['active_id'] . ') will be finished.');
                        $this->finishPassForUser($data['active_id'], $this->test_ending_times[$test_id]['obj_fi']);
                        $can_not_be_finished = false;
                    }
                } else {
                    $this->log->info('Test (' . $test_id . ') has no processing time.');
                }

                if ($can_not_be_finished) {
                    $this->log->info('Test session with active id (' . $data['active_id'] . ') can not be finished by this cron job.');
                }
            }
        }
    }

    protected function finishPassForUser($active_id, $obj_id): void
    {
        $processLocker = $this->processLockerFactory->withContextId((int) $active_id)->getLocker();

        $pass_finisher = new ilTestPassFinishTasks($active_id, $obj_id);
        $pass_finisher->performFinishTasks($processLocker);

        $this->log->info('Test session with active id (' . $active_id . ') and obj_id (' . $obj_id . ') is now finished.');
    }
}
