<?php
namespace ILIAS\MyStaff\ListCertificates;
use Certificate\API\Data\UserCertificateDto;
use Certificate\API\Filter\UserDataFilter;
use Certificate\API\UserCertificateAPI;
use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilLPStatus;
use ilMStListCertificatesGUI;
use ilMyStaffGUI;
use ilOrgUnitOperation;
use ilOrgUnitOperationContext;

/**
 * Class ilMStListCertificates
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCertificates {

    /**
     * @var Container
     */
    protected $dic;


    /**
     * ilMStListCertificates constructor.
     *
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

	/**
	 * @param array $arr_usr_ids
	 * @param array $options
	 *
	 * @return UserCertificateDto[]
	 */
	public function getData(array $options = array()) {
		//Permission Filter
		$operation_access = ilOrgUnitOperation::OP_VIEW_CERTIFICATES;

		if (!empty($options['filters']['lp_status']) || $options['filters']['lp_status'] === 0) {
			$operation_access = ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS;
		}

		$_options = array(
			'filters' => array(),
			'sort' => array(),
			'limit' => array(),
		);
		$options = array_merge($_options, $options);

        $cert_api = new UserCertificateAPI();

        $data = [];
        $users_per_position = ilMyStaffAccess::getInstance()->getUsersForUserPerPosition($this->dic->user()->getId());
        foreach ($users_per_position as $position_id => $users) {
            $usr_data_filter = new UserDataFilter();
            $usr_data_filter = $usr_data_filter->withUserIds($users);
            $usr_data_filter = $usr_data_filter->withObjIds(ilMyStaffAccess::getInstance()->getIdsForUserAndOperation($this->dic->user()->getId(), $operation_access));
            $data = array_merge($data, $cert_api->getUserCertificateData($usr_data_filter,[ilMyStaffGUI::class,ilMStListCertificatesGUI::class]));
        }

        $unique_cert_data = [];
        foreach($data as $cert_data) {
            /**
             * @var UserCertificateDto $cert_data
             */
            $unique_cert_data[$cert_data->getCertificateId()] = $cert_data;
        }

        return $unique_cert_data;




		//$select .= static::createWhereStatement($arr_usr_ids, $options['filters'], $tmp_table_user_matrix);

/*
		if ($options['sort']) {
			$select .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
		}

		if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
			$select .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
		}
		$result = $this->dic->database()->query($select);
		$crs_data = array();

		while ($crs = $this->dic->database()->fetchAssoc($result)) {
			$list_course = new ilMStListCertificate();
			$list_course->setCrsRefId($crs['crs_ref_id']);
			$list_course->setCrsTitle($crs['crs_title']);
			$list_course->setUsrRegStatus($crs['reg_status']);
			$list_course->setUsrLpStatus($crs['lp_status']);
			$list_course->setUsrLogin($crs['usr_login']);
			$list_course->setUsrLastname($crs['usr_lastname']);
			$list_course->setUsrFirstname($crs['usr_firstname']);
			$list_course->setUsrEmail($crs['usr_email']);
			$list_course->setUsrId($crs['usr_id']);

			$crs_data[] = $list_course;
		}

		return $crs_data;*/
	}


	/**
	 * Returns the WHERE Part for the Queries using parameter $user_ids AND local variable $filters
	 *
	 * @param array  $arr_usr_ids
	 * @param array  $arr_filter
	 * @param string $tmp_table_user_matrix
	 *
	 * @return string
	 */
	protected function createWhereStatement(array $arr_usr_ids, array $arr_filter, $tmp_table_user_matrix) {
		$where = array();

		$where[] = '(crs_ref.ref_id, usr_data.usr_id) IN (SELECT * FROM ' . $tmp_table_user_matrix . ')';

		if (count($arr_usr_ids)) {
			$where[] = $this->dic->database()->in('usr_data.usr_id', $arr_usr_ids, false, 'integer');
		}

		if (!empty($arr_filter['crs_title'])) {
			$where[] = '(crs.title LIKE ' . $this->dic->database()->quote('%' . $arr_filter['crs_title'] . '%', 'text') . ')';
		}

		if ($arr_filter['course'] > 0) {
			$where[] = '(crs_ref.ref_id = ' . $this->dic->database()->quote($arr_filter['course'], 'integer') . ')';
		}

		if (!empty($arr_filter['lp_status']) || $arr_filter['lp_status'] === 0) {

			switch ($arr_filter['lp_status']) {
				case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
					//if a user has the lp status not attempted it could be, that the user hase no records in table ut_lp_marks
					$where[] = '(lp_status = ' . $this->dic->database()->quote($arr_filter['lp_status'], 'integer') . ' OR lp_status is NULL)';
					break;
				default:
					$where[] = '(lp_status = ' . $this->dic->database()->quote($arr_filter['lp_status'], 'integer') . ')';
					break;
			}
		}

		if (!empty($arr_filter['memb_status'])) {
			$where[] = '(reg_status = ' . $this->dic->database()->quote($arr_filter['memb_status'], 'integer') . ')';
		}

		if (!empty($arr_filter['user'])) {
			$where[] = "(" . $this->dic->database()->like("usr_data.login", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $this->dic->database()
					->like("usr_data.firstname", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $this->dic->database()
					->like("usr_data.lastname", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $this->dic->database()
					->like("usr_data.email", "text", "%" . $arr_filter['user'] . "%") . ") ";
		}

		if (!empty($arr_filter['org_unit'])) {
			$where[] = 'usr_data.usr_id IN (SELECT user_id FROM il_orgu_ua WHERE orgu_id = ' . $this->dic->database()
					->quote($arr_filter['org_unit'], 'integer') . ')';
		}

		if (!empty($where)) {
			return ' WHERE ' . implode(' AND ', $where) . ' ';
		} else {
			return '';
		}
	}
}
