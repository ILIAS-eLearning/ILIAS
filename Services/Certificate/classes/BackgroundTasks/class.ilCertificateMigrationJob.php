<?php
/*
  +----------------------------------------------------------------------------+
  | ILIAS open source                                                          |
  +----------------------------------------------------------------------------+
  | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
  |                                                                            |
  | This program is free software; you can redistribute it and/or              |
  | modify it under the terms of the GNU General Public License                |
  | as published by the Free Software Foundation; either version 2             |
  | of the License, or (at your option) any later version.                     |
  |                                                                            |
  | This program is distributed in the hope that it will be useful,            |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
  | GNU General Public License for more details.                               |
  |                                                                            |
  | You should have received a copy of the GNU General Public License          |
  | along with this program; if not, write to the Free Software                |
  | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
  +----------------------------------------------------------------------------+
*/

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;

//include_once "./Services/Certificate/classes/Gui/class.ilCertificateFactory.php";
include_once "./Services/Certificate/classes/BackgroundTasks/class.ilMigrationJobDefinitions.php";
include_once "./Services/Certificate/classes/class.ilCertificate.php";
include_once "./Services/Tracking/classes/class.ilObjUserTracking.php";
include_once "./Services/Tracking/classes/class.ilObjUserTracking.php";
include_once "./Services/Tracking/classes/class.ilLPStatus.php";
include_once "./Modules/Course/classes/class.ilCourseCertificateAdapter.php";
include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
include_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";
include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php";
include_once "./Modules/ScormAicc/classes/class.ilSCORMCertificateAdapter.php";
include_once "./Modules/Test/classes/class.ilObjTest.php";
include_once "./Modules/Test/classes/class.ilTestSessionFactory.php";
include_once "./Modules/Exercise/classes/class.ilObjExercise.php";
include_once "./Modules/Course/classes/class.ilObjCourse.php";
include_once "./Modules/Course/classes/class.ilCourseParticipants.php";

/**
 * Class ilCertificateMigrationJob
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class ilCertificateMigrationJob extends AbstractJob
{
    /** @var string */
    protected $db_table;

    /** @var int */
    protected $user_id;

    /** @var \ilTree */
    protected $tree;

    /** @var \ilDB */
    protected $db;

    /**
     * @param \ILIAS\BackgroundTasks\Value[] $input
     * @param Observer                       $observer
     * @return \ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue
     */
    public function run(Array $input, Observer $observer)
    {
        global $DIC;

        $this->user_id = $input[0]->getValue();

        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();
        $this->db_table = \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_JOB_TABLE;

        $task_informations = $this->getTaskInformations();
        if ($task_informations['lock'] == true) {
            // @TODO stop with output
        }
        if ($task_informations['state'] === \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_INIT) {
            // @TODO stop with output
        }
        if ($task_informations['state'] === \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_RUNNING) {
            // @TODO stop with output
        }

        $this->updateState(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_INIT);

        $certificates = [];
        $output = new IntegerValue();

        $this->logMessage('startet at ' . ($st_time = date('d.m.Y H:i:s')), 'debug');
        $this->updateState(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_RUNNING);

        try {
            // collect all data
            $certificates['scorm'] = $this->getScormCertificates();
            $observer->heartbeat();
            $certificates['test'] = $this->getTestCertificates();
            $observer->heartbeat();
            $certificates['exercise'] = $this->getExerciseCertificates();
            $observer->heartbeat();
            $certificates['course'] = $this->getCourseCertificates();
            $observer->heartbeat();

        } catch (\Exception $e) {
            $this->logMessage($e->getMessage(), 'error');
            $this->updateState(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FAILED);
            $output->setValue((int)$e->getCode());
            return $output;
        }

        try {

            // @TODO what to do next?

        } catch (\Exception $e) {
            $this->logMessage($e->getMessage(), 'error');
            $this->updateState(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FAILED);
            $output->setValue((int)$e->getCode());
            return $output;
        }

        $output->setValue(200);

        $this->logMessage('finished at ' . ($f_time = date('d.m.Y H:i:s')) . ' after ' . (strtotime($f_time) - strtotime($st_time)) . ' seconds', 'debug');
        $this->updateState(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FINISHED);

        return $output;
    }

    /**
     * @return bool returns true iff the job's output ONLY depends on the input. Stateless task
     *              results may be cached!
     */
    public function isStateless()
    {
        return true;
    }

    /**
     * @return int the amount of seconds this task usually takes. If your task-duration scales
     *             with the the amount of data, try to set a possible high value of try to
     *             calculate it. If a task duration exceeds this value, it will be displayed as
     *             "possibly failed" to the user
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 300; // @TODO mesure time and modify this value
    }

    /**
     * @return Type[] Classof the Values
     */
    public function getInputTypes()
    {
        return [
            new SingleType(IntegerValue::class),
        ];
    }

    /**
     * @return Type
     */
    public function getOutputType()
    {
        return new SingleType(IntegerValue::class);
    }

    /**
     * @param $user_id
     * @return array
     */
    public function getTaskInformations()
    {
        global $DIC;

        $db = $DIC->database();

        $result = $db->queryF(
            'select * from ' . $this->db_table . ' where user_id = %s',
            ['integer'],
            [$this->user_id]
        );
        if ($result->numRows() == 1)
        {
            $data = $db->fetchAssoc($result);
            return $data;
        }
        return [];
    }

    /**
     * @param $state
     * @return void
     */
    public function updateState($state)
    {
        if (empty($this->getTaskInformations())) {
            $this->db->insert($this->db_table, [
                'id' => $this->db->nextId($this->db_table),
                'usr_id' => $this->user_id,
                'lock' => true,
                'found_items' => 0,
                'processed_items' => 0,
                'progress' => 0,
                'state' => $state,
                'started_ts' => strtotime('now'),
                'finished_ts' => null,
            ]);
        } else {
            $this->db->update($this->db_table, ['state' => $state], ['usr_id' => $this->user_id]);
        }
    }

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    protected function logMessage($message, $type = 'info')
    {
        $m_prefix = '[BackgroundTask][MigrationJob] ';
        switch ($type) {
            case 'critical':
                \ilLoggerFactory::getLogger('cert')->critical($m_prefix . $message);
                break;
            case 'error':
                \ilLoggerFactory::getLogger('cert')->error($m_prefix . $message);
                break;
            case 'warning':
                \ilLoggerFactory::getLogger('cert')->warning($m_prefix . $message);
                break;
            case 'notice':
                \ilLoggerFactory::getLogger('cert')->notice($m_prefix . $message);
                break;
            case 'info':
                \ilLoggerFactory::getLogger('cert')->info($m_prefix . $message);
                break;
            case 'debug':
                \ilLoggerFactory::getLogger('cert')->debug($m_prefix . $message);
                break;
        }
    }

    /**
     * Get certificates for scorm objects
     * @return array
     */
    protected function getScormCertificates()
    {
        $this->logMessage('trying to get scorm certificates for user: ' . $this->user_id);

        $data = array();

        if (\ilCertificate::isActive())
        {
            $obj_ids = array();
            $root = $this->tree->getNodeData($this->tree->getRootId());
            foreach($this->tree->getSubTree($root, true, "sahs") as $node)
            {
                $obj_ids[] = $node["obj_id"];
            }
            if ($obj_ids)
            {

                $lp_active = \ilObjUserTracking::_enabledLearningProgress();
                foreach(\ilCertificate::areObjectsActive($obj_ids) as $obj_id => $active)
                {
                    if ($active)
                    {
                        $type = \ilObjSAHSLearningModule::_lookupSubType($obj_id);
                        if ($type == "scorm")
                        {
                            $lm = new \ilObjSCORMLearningModule($obj_id, false);
                        }
                        else
                        {
                            $lm = new \ilObjSCORM2004LearningModule($obj_id, false);
                        }

//                        $factory = new \ilCertificateFactory();
//
//                        try {
//                            $certificate = $factory->create($lm);
//                        } catch (\Exception $e) {
//                            $this->logMessage('Error getting certificate for object with id \'' . $obj_id . '\' and type \'' . $type . '\'.');
//                            continue;
//                        }
//
//                        if ($certificate->isComplete())
                        $adapter = new \ilSCORMCertificateAdapter($lm); // old method
                        if (\ilCertificate::_isComplete($adapter))       // old method
                        {
                            $lpdata = $completed = false;
                            if ($lp_active)
                            {
                                $completed = \ilLPStatus::_hasUserCompleted($obj_id, $this->user_id);
                                $lpdata = true;
                            }
                            if (!$lpdata)
                            {
                                switch ($type)
                                {
                                    case "scorm":
                                        $completed = \ilObjSCORMLearningModule::_getCourseCompletionForUser($obj_id, $this->user_id);
                                        break;

                                    case "scorm2004":
                                        $completed = \ilObjSCORM2004LearningModule::_getCourseCompletionForUser($obj_id, $this->user_id);
                                        break;
                                }
                            }

                            $data[] = array("id" => $obj_id,
                                "title" => \ilObject::_lookupTitle($obj_id),
                                "passed" => (bool)$completed);
                        }
                    }
                }
            }
        }

        $this->logMessage('got ' . count($data) . ' scorm certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for test objects
     * @return array
     */
    protected function getTestCertificates()
    {
        $this->logMessage('trying to get test certificates for user: ' . $this->user_id);

        $data = array();
        foreach(\ilObjTest::getTestObjIdsWithActiveForUserId($this->user_id) as $test_id)
        {
            $test = new \ilObjTest($test_id, false);
            $session = new \ilTestSessionFactory($test);
            $session = $session->getSession(null);
            if ($test->canShowCertificate($session, $session->getUserId(), $session->getActiveId()))
            {
                $data[] = array("id" => $test_id,
                    "title" => $test->getTitle(),
                    "passed" => $test->getPassed($session->getActiveId()));
            }
        }

        $this->logMessage('got ' . count($data) . ' test certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for exercise objects
     * @return array
     */
    protected function getExerciseCertificates()
    {
        $this->logMessage('trying to get exercise certificates for user: ' . $this->user_id);

        $data = array();
        foreach(\ilObjExercise::_lookupFinishedUserExercises($this->user_id) as $exercise_id => $passed)
        {
            $exc = new \ilObjExercise($exercise_id, false);
            if ($exc->hasUserCertificate($this->user_id))
            {
//                $factory = new \ilCertificateFactory();
//
//                try {
//                    $certificate = $factory->create($exc);
//                } catch (\Exception $e) {
//                    $this->logMessage('Error getting certificate for object with id \'' . $exercise_id . '\' and type \'exercise\'.');
//                    continue;
//                }
//
//                if ($certificate->isComplete())
                include_once('./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php');
                $adapter = new \ilExerciseCertificateAdapter($exc); // old method
                if (\ilCertificate::_isComplete($adapter))           // old method
                {
                    $data[] = array(
                        "id" => $exercise_id,
                        "title" => \ilObject::_lookupTitle($exercise_id),
                        "passed" => $passed
                    );
                }
            }
        }

        $this->logMessage('got ' . count($data) . ' exercise certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for course objects
     * @return array
     */
    protected function getCourseCertificates()
    {
        $this->logMessage('trying to get course certificates for user: ' . $this->user_id);

        $data = array();

        $obj_ids = \ilCourseParticipants::_getMembershipByType($this->user_id, "crs");
        if ($obj_ids)
        {
            \ilCourseCertificateAdapter::_preloadListData([$this->user_id], $obj_ids);

            foreach($obj_ids as $crs_id)
            {
                if (\ilCourseCertificateAdapter::_hasUserCertificate($this->user_id, $crs_id))
                {
//                    $courseObject = new \ilObjCourse($crs_id, false);
//                    $factory = new \ilCertificateFactory();
//
//                    try {
//                        $certificate = $factory->create($courseObject);
//                    } catch (\Exception $e) {
//                        $this->logMessage('Error getting certificate for object with id \'' . $crs_id . '\' and type \'course\'.');
//                        continue;
//                    }
//
//                    if ($certificate->isComplete())
                    $crs = new \ilObjCourse($crs_id, false); // old method
                    $adapter = new \ilCourseCertificateAdapter($crs);        // old method
                    if (\ilCertificate::_isComplete($adapter))                // old method
                    {
                        $data[] = array("id" => $crs_id,
                            "title" => \ilObject::_lookupTitle($crs_id),
                            "passed" => true);
                    }
                }
            }
        }

        $this->logMessage('got ' . count($data) . ' course certificates for user: ' . $this->user_id);
        return $data;
    }

}