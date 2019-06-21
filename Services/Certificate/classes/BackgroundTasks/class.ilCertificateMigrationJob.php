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

	/**
	 * @param \ILIAS\BackgroundTasks\Value[] $input
	 * @param Observer                       $observer
	 * @return \ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue
	 * @throws \ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException
	 */
	public function run(Array $input, Observer $observer): \ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue
	{
		global $DIC;

		$this->user_id = $input[0]->getValue();

		$this->tree = $DIC->repositoryTree();
		$this->db = $DIC->database();
		$this->db_table = \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_JOB_TABLE;
		$this->event_handler = $DIC['ilAppEventHandler'];
		$this->task_factory = $DIC->backgroundTasks()->taskFactory();
		$this->logger = $DIC->logger()->cert();

		$certificates = [];
		$output = new IntegerValue();

		$this->logger->debug('Startet at ' . ($st_time = date('d.m.Y H:i:s')) . ' for user with id: ' . $this->user_id);

		$task_informations = $this->getTaskInformations();
		if(empty($task_informations)) {
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

		$types = array(
			'test',
			'scorm',
			'exercise',
			'course'
		);

		try {
			// collect all data
			$this->logger->info('Start collection certificate data for user: ' . $this->user_id);

			foreach ($types as $type) {
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

		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
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
			$this->logger->info('Start preparing certificate informations for user: ' . $this->user_id);

			foreach ($types as $type) {
				$data = $this->prepareCertificate(
					$observer,
					$certificates,
					$type,
					$processed_items,
					$found_items
				);

				$processed_items = $data['processed_items'];
				$certificates    = $data['certificates_data'];
			}

			$this->logger->info('Finished preparing certificate informations for user: ' . $this->user_id);

		} catch (\Exception $e) {
			$this->logger->error($e->getMessage(), 'error');
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
			$this->logger->info('Start migrating certificates for user: ' . $this->user_id);

			foreach ($certificates as $type => $certs) {
				if(!empty($certs)) {
					foreach ($certs as $cert) {
						$this->logger->info('migrate cert for truple (type, obj_id, user_id): ' . implode(
							', ', [$type, $cert['obj_id'], $cert['user_id']]
							));
						$this->event_handler->raise(
							'Services/Certificate',
							'migrateUserCertificate',
							$cert
						);
						$migrated_items++;
					}
					$this->updateTask([
						'migrated_items' => $migrated_items,
						'progress' => $this->measureProgress($found_items, $processed_items, $migrated_items)
					]);
				}
			}

			$this->logger->info('Finished migrating certificates for user: ' . $this->user_id);

		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
			$this->updateTask([
				'lock' => false,
				'migrated_items' => $migrated_items,
				'state' => \ilCertificateMigrationJobDefinitions::CERT_MIGRATION_STATE_FAILED
			]);
			$output->setValue((int)$e->getCode());
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

		$DIC->user()->writePref('cert_migr_finished', 1);

		return $output;
	}

	/**
	 * @return bool returns true iff the job's output ONLY depends on the input. Stateless task
	 *              results may be cached!
	 */
	public function isStateless(): bool
	{
		return true;
	}

	/**
	 * @return int the amount of seconds this task usually takes. If your task-duration scales
	 *             with the the amount of data, try to set a possible high value of try to
	 *             calculate it. If a task duration exceeds this value, it will be displayed as
	 *             "possibly failed" to the user
	 */
	public function getExpectedTimeOfTaskInSeconds(): int
	{
		return 100;
	}

	/**
	 * @return Type[] Classof the Values
	 */
	public function getInputTypes(): array
	{
		return [
			new SingleType(IntegerValue::class),
		];
	}

	/**
	 * @return Type
	 */
	public function getOutputType(): SingleType
	{
		return new SingleType(IntegerValue::class);
	}

	/**
	 * @return array
	 */
	public function getTaskInformations(): array
	{
		global $DIC;

		$db = $DIC->database();

		$result = $db->queryF(
			'SELECT * FROM ' . $this->db_table . ' WHERE usr_id = %s',
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
	 * @param string $state
	 * @return void
	 */
	protected function updateState(string $state)
	{
		if (empty($this->getTaskInformations())) {
			$this->initTask();
		}
		$this->logger->debug('Update entry for user with id: ' . $this->user_id);
		$this->db->update($this->db_table, ['state' => ['text', $state] ], ['usr_id' => ['integer', $this->user_id] ]);
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
		$this->logger->debug('Update data: ' . json_encode($updata));
		if(!empty($updata)) {
			$this->logger->debug('Update entry for user with id: ' . $this->user_id);
			$this->db->update($this->db_table, $updata, ['usr_id' => ['integer', $this->user_id] ]);
		}
	}

	/**
	 * @param int $found
	 * @param int $processed
	 * @param int $migrated
	 * @return float
	 */
	protected function measureProgress(int $found, int $processed = 0, int $migrated = 0): float
	{
		return (float)(100 / $found * (($processed + $migrated) / 2));
	}

	/**
	 * Get certificates for scorm objects
	 * @return array
	 */
	protected function getScormCertificates(): array
	{
		$this->logger->info('Trying to get scorm certificates for user: ' . $this->user_id);

		$data = array();

		if (\ilCertificate::isActive())
		{
			$lp_active = ilObjUserTracking::_enabledLearningProgress();
			$obj_ids = array();
			$root = $this->tree->getNodeData($this->tree->getRootId());
			foreach($this->tree->getSubTree($root, true, "sahs") as $node)
			{
				$obj_ids[] = $node["obj_id"];
			}
			if ($obj_ids)
			{
				foreach(\ilCertificate::areObjectsActive($obj_ids) as $objectId => $active)
				{
					if ($active)
					{
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

						$adapter = new \ilSCORMCertificateAdapter($object);
						$data = $this->createCertificateData($objectId, $adapter, $object, $data);
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
	protected function getTestCertificates(): array
	{
		$this->logger->info('Trying to get test certificates for user: ' . $this->user_id);

		$data = array();

		foreach(\ilObjTest::getTestObjIdsWithActiveForUserId($this->user_id) as $objectId)
		{
			$object = \ilObjectFactory::getInstanceByObjId($objectId, false);
			if (!$object || !($object instanceof \ilObjTest)) {
				$this->logger->debug('Found inconsistent object, skipped migration: ' . $objectId);
				continue;
			}

			$session = new \ilTestSessionFactory($object);
			$session = $session->getSession(null);
			if ($object->canShowCertificate($session, $session->getUserId(), $session->getActiveId()))
			{
				$adapter = new \ilTestCertificateAdapter($object);
				$data = $this->createCertificateData($objectId, $adapter, $object, $data);
			}
		}

		$this->logger->info('Got ' . count($data) . ' test certificates for user: ' . $this->user_id);
		return $data;
	}

	/**
	 * Get certificates for exercise objects
	 * @return array
	 */
	protected function getExerciseCertificates(): array
	{
		$this->logger->info('Trying to get exercise certificates for user: ' . $this->user_id);

		$data = array();

		foreach(\ilObjExercise::_lookupFinishedUserExercises($this->user_id) as $objectId => $passed)
		{
			$object = \ilObjectFactory::getInstanceByObjId($objectId, false);
			if (!$object || !($object instanceof \ilObjExercise)) {
				$this->logger->debug('Found inconsistent object, skipped migration: ' . $objectId);
				continue;
			}

			$adapter = new \ilExerciseCertificateAdapter($object);
			if ($adapter->hasUserCertificate($this->user_id))
			{
				$data = $this->createCertificateData($objectId, $adapter, $object, $data);
			}
		}

		$this->logger->info('Got ' . count($data) . ' exercise certificates for user: ' . $this->user_id);
		return $data;
	}

	/**
	 * Get certificates for course objects
	 * @return array
	 */
	protected function getCourseCertificates(): array
	{
		$this->logger->info('Trying to get course certificates for user: ' . $this->user_id);

		$data = array();

		$obj_ids = \ilCourseParticipants::_getMembershipByType($this->user_id, "crs");
		if ($obj_ids)
		{
			\ilCourseCertificateAdapter::_preloadListData([$this->user_id], $obj_ids);

			foreach($obj_ids as $objectId)
			{
				if (\ilCourseCertificateAdapter::_hasUserCertificate($this->user_id, $objectId))
				{
					$object = \ilObjectFactory::getInstanceByObjId($objectId, false);
					if (!$object || !($object instanceof \ilObjCourse)) {
						$this->logger->debug('Found inconsistent object, skipped migration: ' . $objectId);
						continue;
					}

					$adapter = new \ilCourseCertificateAdapter($object);
					$data = $this->createCertificateData($objectId, $adapter, $object, $data);
				}
			}
		}

		$this->logger->info('Got ' . count($data) . ' course certificates for user: ' . $this->user_id);
		return $data;
	}

	/**
	 * @param array $cert_data
	 * @return void
	 */
	protected function addCertificateInformation(array $cert_data)
	{
		if(array_key_exists('certificate_path', $cert_data))
		{
			$cert_path = $cert_data['certificate_path'] . "certificate.xml";
			if (file_exists($cert_path) && (filesize($cert_path) > 0))
			{
				$cert_data = $this->addContentAndTimestampToCertificateData($cert_data);
			}
		}

		return $cert_data;
	}

	/**
	 * @param array $cert_data
	 * @return array
	 */
	private function addContentAndTimestampToCertificateData(array $cert_data): array
	{
		$oldDatePresentationStatus = \ilDatePresentation::useRelativeDates();
		\ilDatePresentation::setUseRelativeDates(false);

		$this->logger->debug(
			'Try to render ' . $cert_data['certificate_type'] . ' certificate for obj_id: ' . $cert_data['obj_id'] . ' and user_id: ' . $this->user_id
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

		$this->logger->debug(
			'Successful renedered certificate for (type, obj_id, usr_id): ' . $cert_data['certificate_type'] . ', ' . $cert_data['obj_id'] . ', ' . $this->user_id
		);

		$cert_data['acquired_timestamp'] = time();
		if (true === isset($insert_tags['[DATETIME_COMPLETED_UNIX]'])) {
			$cert_data['acquired_timestamp'] = $insert_tags['[DATETIME_COMPLETED_UNIX]'];
		}

		\ilDatePresentation::setUseRelativeDates($oldDatePresentationStatus);

		$cert_data['certificate_content'] = $xslfo;

		return $cert_data;
	}

	/**
	 * @param $objectId
	 * @param $adapter
	 * @param $object
	 * @param $data
	 * @return array
	 */
	private function createCertificateData(
		$objectId,
		\ilCertificateAdapter $adapter,
		\ilObject $object,
		array $data
	): array
	{
		if (\ilCertificate::isActive() && \ilCertificate::isObjectActive($objectId)) {
			if (file_exists($adapter->getCertificatePath())) {
				$cert_path = $adapter->getCertificatePath();
				$xsl_path = $cert_path . "certificate.xml";

				if (file_exists($xsl_path) && (filesize($xsl_path) > 0)) {
					$type = $object->getType();

					$this->logger->debug(sprintf('Found certificate for object id "%s" (type: "%s") and user id "%s"', $objectId, $type, $this->user_id));

					$webdir = $cert_path . "background.jpg";

					$background_image_path = str_replace(
						\ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR),
						'',
						$webdir
					);

					$data[] = array(
						"obj_id" => $objectId,
						"user_id" => $this->user_id,
						"certificate_path" => $cert_path,
						"certificate_type" => $type,
						"background_image_path" => $background_image_path,
						"acquired_timestamp" => null,
						"ilias_version" => ILIAS_VERSION_NUMERIC,
						"certificate_content" => null,
					);
				}
			}
		}

		return $data;
	}

	/**
	 * @param Observer $observer
	 * @param array $certificates
	 * @param string $type
	 * @param int $processed_items
	 * @param int $found_items
	 * @return array
	 */
	protected function prepareCertificate(
		Observer $observer,
		array $certificates,
		string $type,
		int $processed_items,
		int $found_items
	): array
	{
		if (!empty($certificates[$type])) {
			$this->logger->info(sprintf('Start preparing "%s" certificates', $type));

			foreach ($certificates[$type] as $id => $certificateData) {
				$certificates[$type][$id] = $this->addCertificateInformation($certificateData);
				$processed_items++;
			}

			$this->updateTask([
				'processed_items' => $processed_items,
				'progress' => $this->measureProgress($found_items, $processed_items)
			]);

			$observer->heartbeat();
			$this->logger->info(sprintf('Finished preparing "%s" certificates', $type));
		}

		return array(
			'processed_items'   => $processed_items,
			'certificates_data' => $certificates
		);
	}
}
