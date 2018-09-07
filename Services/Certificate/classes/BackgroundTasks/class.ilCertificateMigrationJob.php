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

include_once "./Services/Certificate/classes/BackgroundTasks/class.ilMigrationJobDefinitions.php";
include_once "./Services/Certificate/classes/class.ilCertificate.php";
include_once "./Services/Tracking/classes/class.ilObjUserTracking.php";
include_once "./Services/Tracking/classes/class.ilObjUserTracking.php";
include_once "./Services/Tracking/classes/class.ilLPStatus.php";
include_once "./Services/Utilities/classes/class.ilUtil.php";
include_once "./Services/User/classes/class.ilUserDefinedFields.php";
require_once "./Services/MathJax/classes/class.ilMathJax.php";
include_once "./Services/WebServices/RPC/classes/class.ilRpcClientFactory.php";
include_once "./Modules/Course/classes/class.ilCourseCertificateAdapter.php";
include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
include_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";
include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php";
include_once "./Modules/ScormAicc/classes/class.ilSCORMCertificateAdapter.php";
include_once "./Modules/Test/classes/class.ilObjTest.php";
include_once "./Modules/Test/classes/class.ilTestSessionFactory.php";
include_once "./Modules/Exercise/classes/class.ilObjExercise.php";
include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
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

    /** @var ilAppEventHandler $ilAppEventHandler */
    protected $event_handler;

    /**
     * @param \ILIAS\BackgroundTasks\Value[] $input
     * @param Observer                       $observer
     * @return \ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue
     * @throws \ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException
     */
    public function run(Array $input, Observer $observer)
    {
        global $DIC;

        $this->user_id = $input[0]->getValue();

        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();
        $this->db_table = \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_JOB_TABLE;
        $this->event_handler = $DIC['ilAppEventHandler'];

        $certificates = [];
        $output = new IntegerValue();

        $this->logMessage('Startet at ' . ($st_time = date('d.m.Y H:i:s')) . ' for user with id: ' . $this->user_id, 'debug');

        $task_informations = $this->getTaskInformations();
        if(empty($task_informations)) {
            $this->initTask();
        }
        if ($task_informations['state'] === \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_RUNNING) {
            $this->logMessage('Parallel execution protection. Stopped task for user ' . $this->user_id . ', because it is already running.');
            $output->setValue(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_RETURN_ALREADY_RUNNING);
            return $output;
        }
        if ($task_informations['lock'] == true) {
            // we should never get here if no fatal error occures
            $this->logMessage('Parallel execution protection. Stopped task for user ' . $this->user_id . ', because it is locked.');
            $output->setValue(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_RETURN_LOCKED);
            return $output;
        }

        $this->updateTask([
            'lock' => true,
            'state' => \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_RUNNING
        ]);

        $found_items = 0;
        try {
            // collect all data
            $this->logMessage('Start collection certificate data for user: ' . $this->user_id);

            $certificates['scorm'] = $this->getScormCertificates();
            $found_items += count($certificates['scorm']);
            $observer->heartbeat();

            $certificates['test'] = $this->getTestCertificates();
            $found_items += count($certificates['test']);
            $observer->heartbeat();

            $certificates['exercise'] = $this->getExerciseCertificates();
            $found_items += count($certificates['exercise']);
            $observer->heartbeat();

            $certificates['course'] = $this->getCourseCertificates();
            $found_items += count($certificates['course']);
            $observer->heartbeat();

            $this->updateTask(['found_items' => $found_items]);
            $this->logMessage('Found overall ' . $found_items . ' items for user with id: ' . $this->user_id, 'debug');
            $this->logMessage('Finished collecting certificate data for user: ' . $this->user_id);

        } catch (\Exception $e) {
            $this->logMessage($e->getMessage(), 'error');
            $this->updateTask([
                'lock' => false,
                'found_items' => $found_items,
                'state' => \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FAILED
            ]);
            $output->setValue((int)$e->getCode());
            return $output;
        }

        $processed_items = 0;
        try {
            // prepare all data
            $this->logMessage('Start preparing certificate informations for user: ' . $this->user_id);

            if(!empty($certificates['scorm'])) {
                $this->logMessage('Start preparing scorm certificates');
                foreach ($certificates['scorm'] as &$scorm) {
                    $this->getCertificateInformations($scorm);
                    $processed_items++;
                }
                $this->updateTask(['progress' => $this->measureProgress($found_items, $processed_items)]);
                $observer->heartbeat();
                $this->logMessage('Finished preparing scorm certificates');
            }

            if(!empty($certificates['test'])) {
                $this->logMessage('Start preparing test certificates');
                foreach ($certificates['test'] as &$test) {
                    $this->getCertificateInformations($test);
                    $processed_items++;
                }
                $this->updateTask(['progress' => $this->measureProgress($found_items, $processed_items)]);
                $observer->heartbeat();
                $this->logMessage('Finished preparing test certificates');
            }

            if(!empty($certificates['exercise'])) {
                $this->logMessage('Start preparing exercise certificates');
                foreach ($certificates['exercise'] as &$exercise) {
                    $this->getCertificateInformations($exercise);
                    $processed_items++;
                }
                $this->updateTask(['progress' => $this->measureProgress($found_items, $processed_items)]);
                $observer->heartbeat();
                $this->logMessage('Finished preparing exercise certificates');
            }

            if(!empty($certificates['course'])) {
                $this->logMessage('Start preparing course certificates');
                foreach ($certificates['course'] as &$course) {
                    $this->getCertificateInformations($course);
                    $processed_items++;
                }
                $this->updateTask(['progress' => $this->measureProgress($found_items, $processed_items)]);
                $observer->heartbeat();
                $this->logMessage('Finished preparing course certificates');
            }

            $this->logMessage('Finished preparing certificate informations for user: ' . $this->user_id);

            // @TODO trigger event

        } catch (\Exception $e) {
            $this->logMessage($e->getMessage(), 'error');
            $this->updateTask([
                'lock' => false,
                'processed_items' => $processed_items,
                'state' => \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FAILED
            ]);
            $output->setValue((int)$e->getCode());
            return $output;
        }

        $migrated_items = 0;
        try {
            // call event for migrating certificates
            $this->logMessage('Start migrating certificates for user: ' . $this->user_id);

            foreach ($certificates as $type => $certs) {
                if(!empty($certs)) {
                    foreach ($certs as $cert) {
                        $this->logMessage('migrate cert for truple (type, obj_id, user_id): ' . implode(
                            '', [$type, $cert['obj_id'], $cert['user_id']]
                            ));
                        $this->event_handler->raise(
                            'Services/Certificate',
                            'migrateUserCertificate',
                            $cert
                        );
                        $migrated_items++;
                    }
                    $this->updateTask(['progress' => $this->measureProgress($found_items, $processed_items, $migrated_items)]);
                }
            }

            $this->logMessage('Finished migrating certificates for user: ' . $this->user_id);

        } catch (\Exception $e) {
            $this->logMessage($e->getMessage(), 'error');
            $this->updateTask([
                'lock' => false,
                'migrated_items' => $migrated_items,
                'state' => \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FAILED
            ]);
            $output->setValue((int)$e->getCode());
            return $output;
        }

        $output->setValue(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_RETURN_SUCCESS);

        $this->logMessage('Finished at ' . ($f_time = date('d.m.Y H:i:s')) . ' after ' . (strtotime($f_time) - strtotime($st_time)) . ' seconds', 'debug');
        $this->updateTask([
            'lock' => false,
            'finished_ts' => $f_time,
            'processed_items' => $processed_items,
            'state' => \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FINISHED
        ]);

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
        return 100;
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
     * @return array|mixed
     */
    public function getTaskInformations()
    {
        global $DIC;

        $db = $DIC->database();

        $result = $db->queryF(
            'select * from ' . $this->db_table . ' where usr_id = %s',
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
    protected function updateState($state)
    {
        if (empty($this->getTaskInformations())) {
            $this->initTask();
        }
        $this->logMessage('Update entry for user with id: ' . $this->user_id, 'debug');
        $this->db->update($this->db_table, ['state' => ['text', $state] ], ['usr_id' => ['integer', $this->user_id] ]);
    }

    protected function initTask()
    {
        $this->logMessage('Insert new entry for user with id: ' . $this->user_id, 'debug');
        $this->db->insert($this->db_table, [
            'id' => ['integer', $this->db->nextId($this->db_table)],
            'usr_id' => ['integer', $this->user_id],
            'lock' => ['integer', false],
            'found_items' => ['integer', 0],
            'processed_items' => ['integer', 0],
            'migrated_items' => ['integer', 0],
            'progress' => ['integer', 0],
            'state' => ['text', \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_INIT],
            'started_ts' => ['integer', strtotime('now')],
            'finished_ts' => null,
        ]);
    }

    /**
     * @param array $data
     * @return void
     */
    protected function updateTask(array $data)
    {
        $updata = [];
        if(array_key_exists('lock', $data)) {
            $updata['lock'] = ['integer', $data['lock']];
        }
        if(array_key_exists('found_items', $data)) {
            $updata['found_items'] = ['integer', $data['found_items']];
        }
        if(array_key_exists('processed_items', $data)) {
            $updata['processed_items'] = ['integer', $data['processed_items']];
        }
        if(array_key_exists('migrated_items', $data)) {
            $updata['migrated_items'] = ['integer', $data['migrated_items']];
        }
        if(array_key_exists('progress', $data)) {
            $updata['progress'] = ['integer', $data['progress']];
        }
        if(array_key_exists('state', $data)) {
            $updata['state'] = ['text', $data['state']];
        }
        if(array_key_exists('finished_ts', $data)) {
            if(is_string($data['finished_ts'])) {
                $data['finished_ts'] = strtotime($data['finished_ts']);
            }
            $updata['finished_ts'] = ['integer', $data['finished_ts']];
        }
        $this->logMessage('Update data: ' . json_encode($updata), 'debug');
        if(!empty($updata)) {
            $this->logMessage('Update entry for user with id: ' . $this->user_id, 'debug');
            $this->db->update($this->db_table, $updata, ['usr_id' => ['integer', $this->user_id] ]);
        }
    }

    /**
     * @param int $found
     * @param int $processed
     * @param int $migrated
     * @return float|int
     */
    protected function measureProgress($found, $processed = 0, $migrated = 0)
    {
        return (100 / $found * (($processed + $migrated) / 2));
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
        $this->logMessage('Trying to get scorm certificates for user: ' . $this->user_id);

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

                        $adapter = new \ilSCORMCertificateAdapter($lm);

                        $obj_id = $adapter->getCertificateID();
                        if(\ilCertificate::isActive() && isset($obj_id) && \ilCertificate::isObjectActive($obj_id))
                        {
                            if (file_exists($adapter->getCertificatePath()))
                            {
                                $cert_path = $adapter->getCertificatePath();
                                $xsl_path = $cert_path . "certificate.xml";
                                if (file_exists($xsl_path) && (filesize($xsl_path) > 0))
                                {
                                    $this->logMessage('Found scorm certificate with id: ' . $obj_id, 'debug');
                                    $webdir = $cert_path . "background.jpg";
                                    $background_image_path = str_replace(
                                        \ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
                                        \ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
                                        $webdir
                                    );
                                    $data[] = array(
                                        "obj_id" => $obj_id,
                                        "user_id" => $this->user_id,
                                        "certificate_path" => $cert_path,
                                        "certificate_type" => 'sahs',
                                        "background_image_path" => $background_image_path,
                                        "aquired_timestamp" => null,
                                        "ilias_version" => ILIAS_VERSION_NUMERIC,
                                        "certificate_content" => null,
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->logMessage('Got ' . count($data) . ' scorm certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for test objects
     * @return array
     */
    protected function getTestCertificates()
    {
        $this->logMessage('Trying to get test certificates for user: ' . $this->user_id);

        $data = array();

        foreach(\ilObjTest::getTestObjIdsWithActiveForUserId($this->user_id) as $test_id)
        {
            $test = new \ilObjTest($test_id, false);
            $session = new \ilTestSessionFactory($test);
            $session = $session->getSession(null);
            if ($test->canShowCertificate($session, $session->getUserId(), $session->getActiveId()))
            {

                $adapter = new \ilTestCertificateAdapter($test);

                $obj_id = $adapter->getCertificateID();
                if(\ilCertificate::isActive() && isset($obj_id) && \ilCertificate::isObjectActive($obj_id))
                {
                    if (file_exists($adapter->getCertificatePath()))
                    {
                        $cert_path = $adapter->getCertificatePath();
                        $xsl_path = $cert_path . "certificate.xml";
                        if (file_exists($xsl_path) && (filesize($xsl_path) > 0))
                        {
                            $this->logMessage('Found test certificate with id: ' . $test_id, 'debug');
                            $webdir = $cert_path . "background.jpg";
                            $background_image_path = str_replace(
                                \ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
                                \ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
                                $webdir
                            );
                            $data[] = array(
                                "obj_id" => $test_id,
                                "user_id" => $this->user_id,
                                "certificate_path" => $cert_path,
                                "certificate_type" => 'tst',
                                "background_image_path" => $background_image_path,
                                "aquired_timestamp" => null,
                                "ilias_version" => ILIAS_VERSION_NUMERIC,
                                "certificate_content" => null,
                            );
                        }
                    }
                }
            }
        }

        $this->logMessage('Got ' . count($data) . ' test certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for exercise objects
     * @return array
     */
    protected function getExerciseCertificates()
    {
        $this->logMessage('Trying to get exercise certificates for user: ' . $this->user_id);

        $data = array();

        foreach(\ilObjExercise::_lookupFinishedUserExercises($this->user_id) as $exercise_id => $passed)
        {
            $exc = new \ilObjExercise($exercise_id, false);
            if ($exc->hasUserCertificate($this->user_id))
            {

                $adapter = new \ilExerciseCertificateAdapter($exc);

                $obj_id = $adapter->getCertificateID();
                if(\ilCertificate::isActive() && isset($obj_id) && \ilCertificate::isObjectActive($obj_id))
                {
                    if (file_exists($adapter->getCertificatePath()))
                    {
                        $cert_path = $adapter->getCertificatePath();
                        $xsl_path = $cert_path . "certificate.xml";
                        if (file_exists($xsl_path) && (filesize($xsl_path) > 0))
                        {
                            $this->logMessage('Found exercise certificate with id: ' . $obj_id, 'debug');
                            $webdir = $cert_path . "background.jpg";
                            $background_image_path = str_replace(
                                \ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
                                \ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
                                $webdir
                            );
                            $data[] = array(
                                "obj_id" => $exercise_id,
                                "user_id" => $this->user_id,
                                "certificate_path" => $cert_path,
                                "certificate_type" => 'exc',
                                "background_image_path" => $background_image_path,
                                "aquired_timestamp" => null,
                                "ilias_version" => ILIAS_VERSION_NUMERIC,
                                "certificate_content" => null,
                            );
                        }
                    }
                }
            }
        }

        $this->logMessage('Got ' . count($data) . ' exercise certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for course objects
     * @return array
     */
    protected function getCourseCertificates()
    {
        $this->logMessage('Trying to get course certificates for user: ' . $this->user_id);

        $data = array();

        $obj_ids = \ilCourseParticipants::_getMembershipByType($this->user_id, "crs");
        if ($obj_ids)
        {
            \ilCourseCertificateAdapter::_preloadListData([$this->user_id], $obj_ids);

            foreach($obj_ids as $crs_id)
            {
                if (\ilCourseCertificateAdapter::_hasUserCertificate($this->user_id, $crs_id))
                {

                    $crs = new \ilObjCourse($crs_id, false);
                    $adapter = new \ilCourseCertificateAdapter($crs);
                    $obj_id = $adapter->getCertificateID();
                    if(\ilCertificate::isActive() && isset($obj_id) && \ilCertificate::isObjectActive($obj_id))
                    {
                        if (file_exists($adapter->getCertificatePath()))
                        {
                            $cert_path = $adapter->getCertificatePath();
                            $xsl_path = $cert_path . "certificate.xml";
                            if (file_exists($xsl_path) && (filesize($xsl_path) > 0))
                            {
                                $this->logMessage('Found course certificate with id: ' . $crs_id, 'debug');
                                $webdir = $cert_path . "background.jpg";
                                $background_image_path = str_replace(
                                    \ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
                                    \ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
                                    $webdir
                                );
                                $data[] = array(
                                    "obj_id" => $crs_id,
                                    "user_id" => $this->user_id,
                                    "certificate_path" => $cert_path,
                                    "certificate_type" => 'crs',
                                    "background_image_path" => $background_image_path,
                                    "aquired_timestamp" => null,
                                    "ilias_version" => ILIAS_VERSION_NUMERIC,
                                    "certificate_content" => null,
                                );
                            }
                        }
                    }
                }
            }
        }

        $this->logMessage('Got ' . count($data) . ' course certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * @param array $cert_data
     * @return void
     */
    protected function getCertificateInformations(&$cert_data)
    {
        if(array_key_exists('certificate_path', $cert_data))
        {
            $cert_path = $cert_data['certificate_path'] . "certificate.xml";
            if (file_exists($cert_path) && (filesize($cert_path) > 0))
            {
                $cert_data['aquired_timestamp'] = filemtime($cert_path);
                $cert_data['certificate_content'] = $this->renderCertificate($cert_data);
            }
        }
    }

    /**
     * @param array $cert_data
     * @return mixed
     */
    protected function renderCertificate($cert_data)
    {
        \ilDatePresentation::setUseRelativeDates(false);
        $this->logMessage(
            'Try to render ' . $cert_data['certificate_type'] . ' certificate for obj_id: ' . $cert_data['obj_id'] . ' and user_id: ' . $this->user_id,
            'debug'
        );

        $user_data = [];
        // get type specific adapter build data array
        switch ($cert_data['certificate_type']) {
            case 'sahs':
                $type = \ilObjSAHSLearningModule::_lookupSubType($cert_data['obj_id']);
                if ($type == "scorm")
                {
                    $lm = new \ilObjSCORMLearningModule($cert_data['obj_id'], false);
                    $last_access = \ilObjSCORMLearningModule::_lookupLastAccess($lm->getId(), $cert_data['user_id']);
                } else {
                    $lm = new \ilObjSCORM2004LearningModule($cert_data['obj_id'], false);
                    $last_access = \ilObjSCORM2004LearningModule::_lookupLastAccess($lm->getId(), $cert_data['user_id']);
                }
                $adapter = new \ilSCORMCertificateAdapter($lm);
                $user_data['user_data'] = \ilObjUser::_lookupFields($cert_data['user_id']);
                $user_data['last_access'] = $last_access;
                break;
            case 'tst':
                $test = new \ilObjTest($cert_data['obj_id'], false);
                $adapter = new \ilTestCertificateAdapter($test);
                $user_data['active_id'] = $test->getActiveIdOfUser($cert_data['user_id']);
                $user_data['pass'] = ilObjTest::_getResultPass($user_data['active_id']);
                break;
            case 'exc':
                $exc = new \ilObjExercise($cert_data['obj_id'], false);
                $adapter = new \ilExerciseCertificateAdapter($exc);
                $user_data['user_id'] = $cert_data['user_id'];
                break;
            case 'crs':
                $crs = new \ilObjCourse($cert_data['obj_id'], false);
                $adapter = new \ilCourseCertificateAdapter($crs);
                $user_data['user_id'] = $cert_data['user_id'];
                break;
            default:
                return '';
                break;
        }

        // get fields
        $insert_tags = $adapter->getCertificateVariablesForPresentation($user_data);
        $cust_data = new \ilUserDefinedData($adapter->getUserIdForParams($user_data));
        $cust_data = $cust_data->getAll();

        // get field representations
        $user_field_definitions = \ilUserDefinedFields::_getInstance();
        $fds = $user_field_definitions->getDefinitions();
        foreach ($fds as $f)
        {
            if ($f["certificate"])
            {
                $ph = "[#".str_replace(" ", "_", strtoupper($f["field_name"]))."]";
                $insert_tags[$ph] = \ilUtil::prepareFormOutput($cust_data["f_".$f["field_id"]]);
            }
        }

        $xslfo = file_get_contents($cert_data['certificate_path'] . "certificate.xml");

        // render tex as fo graphics
        $xslfo = \ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_PDF)
            ->setRendering(ilMathJax::RENDER_PNG_AS_FO_FILE)
            ->insertLatexImages($xslfo);


        // exchange certificate variables
        if (count($insert_tags) == 0)
        {
            $insert_tags = $adapter->getCertificateVariablesForPreview();
            foreach ($fds as $f)
            {
                if ($f["certificate"])
                {
                    $ph = "[#".str_replace(" ", "_", strtoupper($f["field_name"]))."]";
                    $insert_tags[$ph] = \ilUtil::prepareFormOutput($f["field_name"]);
                }
            }

        }
        foreach ($insert_tags as $var => $value)
        {
            $xslfo = str_replace($var, $value, $xslfo);
        }
        $xslfo = str_replace('[CLIENT_WEB_DIR]', CLIENT_WEB_DIR, $xslfo);

//        $pdf_base64 = \ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($xslfo);

        $this->logMessage(
            'Successful renedered certificate for (type, obj_id, usr_id): ' . $cert_data['certificate_type'] . ', ' . $cert_data['obj_id'] . ', ' . $this->user_id,
            'debug'
        );

        ilDatePresentation::setUseRelativeDates(true);
//        return $pdf_base64->scalar;
        return $xslfo;
    }

}