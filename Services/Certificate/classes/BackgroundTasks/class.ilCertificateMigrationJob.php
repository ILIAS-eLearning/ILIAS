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
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;

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

    /** @var \ILIAS\BackgroundTasks\Task\TaskFactory */
    protected $task_factory;

    /** @var ilLogger */
    private $logger;
    
    private $types = [
        'test',
        'scorm',
        'exercise',
        'course'
    ];

    /**
     * @param \ILIAS\BackgroundTasks\Value[] $input
     * @param Observer                       $observer
     * @return \ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue
     * @throws \ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException
     */
    public function run(array $input, Observer $observer) : \ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue
    {
        global $DIC;

        $this->user_id = $input[0]->getValue();
        $user = new ilObjUser($this->user_id);

        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();
        $this->db_table = \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_JOB_TABLE;
        $this->event_handler = $DIC['ilAppEventHandler'];
        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->logger = $DIC->logger()->cert();

        $certificates = [];
        $output = new IntegerValue();

        $this->logger->debug('Started at ' . ($st_time = date('d.m.Y H:i:s')) . ' for user with id: ' . $this->user_id);

        $task_informations = $this->getTaskInformations();
        if (empty($task_informations)) {
            $this->initTask();
        }
        if ($task_informations['state'] === \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_RUNNING) {
            $this->logger->info('Parallel execution protection. Stopped task for user ' . $this->user_id . ', because it is already running.');
            $output->setValue(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_RETURN_ALREADY_RUNNING);
            return $output;
        }
        if ($task_informations['lock'] == true) {
            // we should never get here if no fatal error occures
            $this->logger->info('Parallel execution protection. Stopped task for user ' . $this->user_id . ', because it is locked.');
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
            $this->logger->info('Start collection certificate data for user: ' . $this->user_id);

            foreach ($this->types as $type) {
                if ($type === 'scorm') {
                    $certificates[$type] = $this->getScormCertificates();
                } elseif ($type === 'test') {
                    $certificates[$type] = $this->getTestCertificates();
                } elseif ($type === 'exercise') {
                    $certificates[$type] = $this->getExerciseCertificates();
                } elseif ($type === 'course') {
                    $certificates[$type] = $this->getCourseCertificates();
                }

                $found_items += count($certificates[$type]);
                $observer->heartbeat();
            }

            $this->updateTask(['found_items' => $found_items]);
            $this->logger->debug('Found overall ' . $found_items . ' items for user with id: ' . $this->user_id);
            $this->logger->info('Finished collecting certificate data for user: ' . $this->user_id);
            
            $processed_items = 0;

            $template_repository = new ilCertificateTemplateRepository($this->db);
            $repository = new ilUserCertificateRepository($this->db);
            $value_replacement = new ilCertificateValueReplacement();

            $this->logger->info('Start migrating certificates for user: ' . $user->getId());

            foreach ($this->types as $type) {
                $this->logger->info(sprintf('Start preparing "%s" certificates', $type));

                $class = 'il' . ucfirst($type) . 'PlaceholderValues';
                $placeholder_values = new $class();
                
                foreach ($certificates[$type] as $certificate_id) {
                    if (\ilCertificate::isObjectActive($certificate_id)) {
                        try {
                            switch (true) {
                                case $type == 'course':
                                    $acquireDate = new ilDateTime(
                                        ilCourseParticipants::getDateTimeOfPassed($certificate_id, $user->getId()),
                                        IL_CAL_DATETIME
                                    );
                                    $acquireTimestamp = $acquireDate->get(IL_CAL_UNIX);
                                    break;
                                case $type == 'test' && !ilObjUserTracking::_enabledLearningProgress():
                                    $test = new ilObjTest($certificate_id, false);
                                    $acquireTimestamp = $test
                                        ->getTestResult($test->getActiveIdOfUser($user->getId()))['test']['result_tstamp'];
                                    break;
                                default:
                                    $acquireDate = new ilDateTime(
                                        ilLPStatus::_lookupStatusChanged($certificate_id, $user->getId()),
                                        IL_CAL_DATETIME
                                    );
                                    $acquireTimestamp = $acquireDate->get(IL_CAL_UNIX);
                            }
                        } catch (Exception $exception) {
                            $this->logger->warning(sprintf('Unable to gather aquired timestamp for certificate %s', $certificate_id));
                            $acquireTimestamp = null;
                        }
                        if ($acquireTimestamp === null || 0 === (int) $acquireTimestamp) {
                            $acquireTimestamp = time();
                        }
                        $template = null;
                        try {
                            $template = $template_repository->fetchFirstCreatedTemplate($certificate_id);
                        } catch (Exception $exception) {
                            $this->logger->warning(sprintf('Unable to gather template for certificate %s. Skipped.', $certificate_id));
                        }
                        if ($template) {
                            $certificate = new ilUserCertificate(
                                $template->getId(),
                                $certificate_id,
                                $type,
                                $user->getId(),
                                $user->getFullname(),
                                $acquireTimestamp,
                                $value_replacement->replace(
                                    $placeholder_values->getPlaceholderValues($user->getId(), $certificate_id),
                                    $template->getCertificateContent()
                                ) ?? '',
                                json_encode($placeholder_values->getPlaceholderValues($user->getId(), $certificate_id)) ?? '',
                                null,
                                $template->getVersion(),
                                ILIAS_VERSION_NUMERIC,
                                true,
                                $template->getBackgroundImagePath() ?? '',
                                $template->getThumbnailImagePath() ?? ''
                            );
                            $repository->save($certificate);
                            $processed_items++;
                        }
                    }
                    $this->updateTask([
                        'processed_items' => $processed_items,
                        'progress' => ($processed_items/$found_items)*100
                    ]);

                    $observer->heartbeat();
                }
                $this->logger->info(sprintf('Finished preparing "%s" certificates', $type));
            }

            $this->logger->info('Finished migrating certificates for user: ' . $user->getId());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), 'error');
            $this->updateTask([
                'lock' => false,
                'processed_items' => $processed_items,
                'state' => \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FAILED
            ]);
            $output->setValue((int) $e->getCode());
            return $output;
        }

        $output->setValue(\ilCertificateMigrationJobDefinitions::CERT_MIGRATION_RETURN_SUCCESS);

        $this->logger->debug('Finished at ' . ($f_time = date('d.m.Y H:i:s')) . ' after ' . (strtotime($f_time) - strtotime($st_time)) . ' seconds');
        $this->updateTask([
            'lock' => false,
            'finished_ts' => $f_time,
            'processed_items' => $processed_items,
            'state' => \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FINISHED
        ]);

        $user->writePref('cert_migr_finished', 1);

        return $output;
    }

    /**
     * @return bool returns true iff the job's output ONLY depends on the input. Stateless task
     *              results may be cached!
     */
    public function isStateless() : bool
    {
        return true;
    }

    /**
     * @return int the amount of seconds this task usually takes. If your task-duration scales
     *             with the the amount of data, try to set a possible high value of try to
     *             calculate it. If a task duration exceeds this value, it will be displayed as
     *             "possibly failed" to the user
     */
    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 100;
    }

    /**
     * @return Type[] Classof the Values
     */
    public function getInputTypes() : array
    {
        return [
            new SingleType(IntegerValue::class),
        ];
    }

    /**
     * @return Type
     */
    public function getOutputType() : SingleType
    {
        return new SingleType(IntegerValue::class);
    }

    /**
     * @return array
     */
    public function getTaskInformations() : array
    {
        global $DIC;

        $db = $DIC->database();

        $result = $db->queryF(
            'SELECT * FROM ' . $this->db_table . ' WHERE usr_id = %s',
            ['integer'],
            [$this->user_id]
        );
        if ($result->numRows() == 1) {
            $data = $db->fetchAssoc($result);
            return $data;
        }
        return [];
    }

    /**
     * @return void
     */
    protected function initTask()
    {
        $this->logger->debug('Insert new entry for user with id: ' . $this->user_id);
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
        if (array_key_exists('lock', $data)) {
            $updata['lock'] = ['integer', $data['lock']];
        }
        if (array_key_exists('found_items', $data)) {
            $updata['found_items'] = ['integer', $data['found_items']];
        }
        if (array_key_exists('processed_items', $data)) {
            $updata['processed_items'] = ['integer', $data['processed_items']];
        }
        if (array_key_exists('migrated_items', $data)) {
            $updata['migrated_items'] = ['integer', $data['migrated_items']];
        }
        if (array_key_exists('progress', $data)) {
            $updata['progress'] = ['integer', $data['progress']];
        }
        if (array_key_exists('state', $data)) {
            $updata['state'] = ['text', $data['state']];
        }
        if (array_key_exists('finished_ts', $data)) {
            if (is_string($data['finished_ts'])) {
                $data['finished_ts'] = strtotime($data['finished_ts']);
            }
            $updata['finished_ts'] = ['integer', $data['finished_ts']];
        }
        $this->logger->debug('Update data: ' . json_encode($updata));
        if (!empty($updata)) {
            $this->logger->debug('Update entry for user with id: ' . $this->user_id);
            $this->db->update($this->db_table, $updata, ['usr_id' => ['integer', $this->user_id] ]);
        }
    }

    /**
     * Get certificates for scorm objects
     * @return array
     */
    protected function getScormCertificates() : array
    {
        $this->logger->info('Trying to get scorm certificates for user: ' . $this->user_id);

        $data = array();

        if (\ilCertificate::isActive()) {
            $lp_active = ilObjUserTracking::_enabledLearningProgress();
            $obj_ids = array();
            $root = $this->tree->getNodeData($this->tree->getRootId());
            foreach ($this->tree->getSubTree($root, true, "sahs") as $node) {
                $obj_ids[] = $node["obj_id"];
            }
            if ($obj_ids) {
                foreach (\ilCertificate::areObjectsActive($obj_ids) as $objectId => $active) {
                    if ($active) {
                        $type = \ilObjSAHSLearningModule::_lookupSubType($objectId);

                        $object = \ilObjectFactory::getInstanceByObjId($objectId, false);
                        if (!$object || !($object instanceof \ilObjSAHSLearningModule)) {
                            $this->logger->debug('Found inconsistent object, skipped migration: ' . $objectId);
                            continue;
                        }
                        $object->setSubType($type);

                        $lpdata = $completed = false;
                        if ($lp_active) {
                            $completed = ilLPStatus::_hasUserCompleted($objectId, $this->user_id);
                            $lpdata = true;
                        }

                        if (!$lpdata) {
                            switch ($type) {
                                case 'scorm':
                                    $completed = ilObjSCORMLearningModule::_getCourseCompletionForUser(
                                        $objectId,
                                        $this->user_id
                                    );
                                    break;

                                case 'scorm2004':
                                    $completed = ilObjSCORM2004LearningModule::_getCourseCompletionForUser(
                                        $objectId,
                                        $this->user_id
                                    );
                                    break;
                            }
                        }

                        if (!$completed) {
                            $this->logger->info(sprintf(
                                'User did not complete SCORM object with obj_id %s, ignoring object for migration ...',
                                $objectId
                            ));
                            continue;
                        }

                        $this->logger->info(sprintf(
                            'User completed SCORM object with obj_id %s, considering object for migration ...',
                            $objectId
                        ));

                        $data[] = $objectId;
                    }
                }
            }
        }

        $this->logger->info('Got ' . count($data) . ' scorm certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for test objects
     * @return array
     */
    protected function getTestCertificates() : array
    {
        $this->logger->info('Trying to get test certificates for user: ' . $this->user_id);

        $data = array();

        foreach (\ilObjTest::getTestObjIdsWithActiveForUserId($this->user_id) as $objectId) {
            $object = \ilObjectFactory::getInstanceByObjId($objectId, false);
            if (!$object || !($object instanceof \ilObjTest)) {
                $this->logger->debug('Found inconsistent object, skipped migration: ' . $objectId);
                continue;
            }

            $session = new \ilTestSessionFactory($object);
            $session = $session->getSession(null);
            if ($object->canShowCertificate($session, $session->getUserId(), $session->getActiveId())) {
                $data[] = $objectId;
            }
        }

        $this->logger->info('Got ' . count($data) . ' test certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for exercise objects
     * @return array
     */
    protected function getExerciseCertificates() : array
    {
        $this->logger->info('Trying to get exercise certificates for user: ' . $this->user_id);

        $data = array();

        foreach (\ilObjExercise::_lookupFinishedUserExercises($this->user_id) as $objectId => $passed) {
            $object = \ilObjectFactory::getInstanceByObjId($objectId, false);
            if (!$object || !($object instanceof \ilObjExercise)) {
                $this->logger->debug('Found inconsistent object, skipped migration: ' . $objectId);
                continue;
            }

            $adapter = new \ilExerciseCertificateAdapter($object);
            if ($adapter->hasUserCertificate($this->user_id)) {
                $data[] = $objectId;
            }
        }

        $this->logger->info('Got ' . count($data) . ' exercise certificates for user: ' . $this->user_id);
        return $data;
    }

    /**
     * Get certificates for course objects
     * @return array
     */
    protected function getCourseCertificates() : array
    {
        $this->logger->info('Trying to get course certificates for user: ' . $this->user_id);

        $data = array();

        $obj_ids = \ilCourseParticipants::_getMembershipByType($this->user_id, "crs");
        if ($obj_ids) {
            \ilCourseCertificateAdapter::_preloadListData([$this->user_id], $obj_ids);

            foreach ($obj_ids as $objectId) {
                if (\ilCourseCertificateAdapter::_hasUserCertificate($this->user_id, $objectId)) {
                    $object = \ilObjectFactory::getInstanceByObjId($objectId, false);
                    if (!$object || !($object instanceof \ilObjCourse)) {
                        $this->logger->debug('Found inconsistent object, skipped migration: ' . $objectId);
                        continue;
                    }

                    $data[] = $objectId;
                }
            }
        }

        $this->logger->info('Got ' . count($data) . ' course certificates for user: ' . $this->user_id);
        return $data;
    }
}
