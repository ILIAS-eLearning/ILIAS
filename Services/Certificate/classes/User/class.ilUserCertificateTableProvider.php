<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateTableProvider
{
	/**
	 * @var
	 */
	private $database;

	/**
	 * @var ilLogger
	 */
	private $logger;

	/**
	 * @var ilCtrl
	 */
	private $controller;

	/**
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateDateHelper|null
	 */
	private $dateHelper;

	/**
	 * @var int
	 */
	private $dateFormat;

	/**
	 * @param ilDBInterface $database
	 * @param ilLogger $logger
	 * @param ilCtrl $controller
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificateDateHelper|null $dateHelper
	 * @param int $dateFormat
	 */
	public function __construct(
		ilDBInterface $database,
		ilLogger $logger,
		ilCtrl $controller,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateDateHelper $dateHelper = null,
		$dateFormat = IL_CAL_UNIX
	) {
		$this->database = $database;
		$this->logger = $logger;
		$this->controller = $controller;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $dateHelper) {
			$dateHelper = new ilCertificateDateHelper();
		}
		$this->dateHelper = $dateHelper;

		$this->dateFormat = $dateFormat;
	}

	/**
	 * @param $userId
	 * @param $params
	 * @param $filter
	 * @return array
	 * @throws ilDateTimeException
	 */
	public function fetchDataSet($userId, $params, $filter)
	{
		$this->logger->info(sprintf('START - Fetching all active certificates for user: "%s"', $userId));

		$sql = 'SELECT id, acquired_timestamp, obj_id FROM il_cert_user_cert WHERE user_id = ' . $this->database->quote($userId,
				'integer') . ' AND currently_active = 1';


		if (array() !== $params) {
			$sql .= $this->getOrderByPart($params, $filter);
		}

		if (isset($params['limit'])) {
			if (!is_numeric($params['limit'])) {
				throw new InvalidArgumentException('Please provide a valid numerical limit.');
			}

			if (!isset($params['offset'])) {
				$params['offset'] = 0;
			} else {
				if (!is_numeric($params['offset'])) {
					throw new InvalidArgumentException('Please provide a valid numerical offset.');
				}
			}

			$this->database->setLimit($params['limit'], $params['offset']);
		}

		$query = $this->database->query($sql);

		$data = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$object = $this->objectHelper->getInstanceByObjId($row['obj_id']);
			$title = $object->getTitle();


			$data['items'][] = array(
				'id' => $row['id'],
				'title' => $title,
				'date' => $this->dateHelper->formatDate($row['acquired_timestamp'], $this->dateFormat),
				'action' => $this->controller->getLinkTargetByClass('ilUserCertificateTableGUI', 'download')
			);
		}

		if (isset($params['limit'])) {
			$cnt_sql = 'SELECT COUNT(*) cnt FROM il_cert_user_cert WHERE user_id = ' . $this->database->quote($userId,
					'integer') . ' AND currently_active = 1';

			$row_cnt = $this->database->fetchAssoc($this->database->query($cnt_sql));

			$data['cnt'] = $row_cnt['cnt'];

			$this->logger->info(sprintf('All active certificates for user: "%s" total: "%s"', $userId,
				count($data['cnt'])));
		}

		$this->logger->debug(sprintf('END - Actual results:', json_encode($data)));

		return $data;
	}

	/**
	 * @param array $params
	 * @param array $filter
	 * @return string
	 */
	protected function getOrderByPart(array $params, array $filter)
	{
		if(isset($params['order_field'])) {
			if(!is_string($params['order_field'])) {
				throw new InvalidArgumentException('Please provide a valid order field.');
			}

			if(!in_array($params['order_field'], array('date', 'id'))) {
				throw new InvalidArgumentException('Please provide a valid order field.');
			}

			if($params['order_field'] == 'date') {
				$params['order_field'] = 'acquired_timestamp';
			}

			if(!isset($params['order_direction'])) {
				$params['order_direction'] = 'ASC';
			}
			else if(!in_array(strtolower($params['order_direction']), array('asc', 'desc'))) {
				throw new InvalidArgumentException('Please provide a valid order direction.');
			}

			return ' ORDER BY ' . $params['order_field'] . ' ' . $params['order_direction'];
		}

		return '';
	}
}
