<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace Certificate\API\Repository;

use Certificate\API\Data\UserCertificateDto;
use Certificate\API\Filter\UserDataFilter;
use ilDBConstants;
use ilUserCertificateApiGUI;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserDataRepository
{
    /** @var \ilDBInterface */
    private $database;

    /** @var \ilLogger */
    private $logger;

    /** @var null|string */
    private $defaultTitle;

    /** @var \ilCtrl */
    private $controller;

    /**
     * @param \ilDBInterface $database
     * @param \ilLogger $logger
     * @param \ilCtrl $controller
     * @param string|null $defaultTitle The default title is use if the title of an repository object could not be
     *                                  determined. This could be the case if the object is deleted from system and
     *                                  mechanisms to store the title of deleted objects (table: object_data_del) failed.
     */
    public function __construct(
        \ilDBInterface $database,
        \ilLogger $logger,
        \ilCtrl $controller,
        string $defaultTitle = null
    ) {
        $this->database = $database;
        $this->logger = $logger;
        $this->controller = $controller;

        if (null === $defaultTitle) {
            global $DIC;
            $defaultTitle = $DIC->language()->txt('certificate_no_object_title');
        }
        $this->defaultTitle = $defaultTitle;
    }

    /**
     * @param UserDataFilter $filter
     * @param array $ilCtrlStack
     * @return array
     */
    public function getUserData(UserDataFilter $filter, array $ilCtrlStack) : array
    {
        $sql = 'SELECT
    cert.pattern_certificate_id,
    cert.obj_id,
    cert.user_id,
    cert.user_name,
    cert.acquired_timestamp,
    cert.currently_active,
    cert.id,
    cert.title,
    cert.ref_id,
    usr_data.firstname,
    usr_data.lastname,
    usr_data.email,
    usr_data.login,
    usr_data.second_email
FROM
' . $this->getQuery($filter);

        $query = $this->database->query($sql);

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $id = (int) $row['id'];

            if (isset($result[$id])) {
                $result[$id]->addRefId((int) $row['ref_id']);
                continue;
            }

            $link = '';
            if ([] !== $ilCtrlStack) {
                $ilCtrlStack[] = ilUserCertificateApiGUI::class;
                $this->controller->setParameterByClass(ilUserCertificateApiGUI::class, 'certificate_id', $id);
                $link = $this->controller->getLinkTargetByClass($ilCtrlStack, ilUserCertificateApiGUI::CMD_DOWNLOAD);
                $this->controller->clearParametersByClass(ilUserCertificateApiGUI::class);
            }

            $dataObject = new UserCertificateDto(
                $id,
                $row['title'] ?? $this->defaultTitle,
                (int) $row['obj_id'],
                (int) $row['acquired_timestamp'],
                (int) $row['user_id'],
                $row['firstname'],
                $row['lastname'],
                $row['login'],
                (string) $row['email'],
                (string) $row['second_email'],
                [(int) $row['ref_id']],
                $link
            );

            $result[$id] = $dataObject;
        }

        if ($filter->getLimitOffset() !== null && $filter->getLimitCount() !== null) {
            $result = array_slice($result, $filter->getLimitOffset(), $filter->getLimitCount());
        }

        return $result;
    }


    /**
     * @param UserDataFilter $filter
     *
     * @return int
     */
    public function getUserCertificateDataMaxCount(UserDataFilter $filter) : int
    {
        $sql = 'SELECT
    COUNT(id) AS count
FROM
' . $this->getQuery($filter, true);

        $query = $this->database->query($sql);

        $max_count = intval($this->database->fetchAssoc($query)["count"]);

        return $max_count;
    }


    /**
     * @param UserDataFilter $filter
     * @param bool           $max_count_only
     *
     * @return string
     */
    private function getQuery(UserDataFilter $filter, bool $max_count_only = false) : string
    {
        $sql = '(
SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.user_id,
  il_cert_user_cert.user_name,
  il_cert_user_cert.acquired_timestamp,
  il_cert_user_cert.currently_active,
  il_cert_user_cert.id,
  object_data.title,
  object_reference.ref_id
FROM il_cert_user_cert
INNER JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
INNER JOIN object_reference ON object_reference.obj_id = il_cert_user_cert.obj_id';

        if ($filter->shouldIncludeDeletedObjects()) {
            $sql .= '
UNION
SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.user_id,
  il_cert_user_cert.user_name,
  il_cert_user_cert.acquired_timestamp,
  il_cert_user_cert.currently_active,
  il_cert_user_cert.id,
  object_data_del.title,
  NULL AS ref_id
FROM il_cert_user_cert
INNER JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id';
        } else {
            $sql .= '
WHERE object_reference.deleted IS NULL';
        }

        $sql .= '
) AS cert
INNER JOIN usr_data ON usr_data.usr_id = cert.user_id
' . $this->createWhereCondition($filter);

        if (!$max_count_only) {
            $sql .= $this->createOrderByClause($filter);
        }

        return $sql;
    }


    /**
     * @param UserDataFilter $filter
     * @return string
     */
    private function createOrderByClause(UserDataFilter $filter) : string
    {
        $sorts = $filter->getSorts();

        if (empty($sorts)) {
            return '';
        }

        $orders = [];

        foreach ($sorts as [$key, $direction]) {
            $direction = $direction === UserDataFilter::SORT_DIRECTION_DESC ? ' DESC' : ' ASC';

            switch (true) {
                case ($key === UserDataFilter::SORT_FIELD_USR_LOGIN):
                    $orders[] = 'usr_data.login' . $direction;
                    break;

                case ($key === UserDataFilter::SORT_FIELD_USR_FIRSTNAME):
                    $orders[] = 'usr_data.firstname' . $direction;
                    break;

                case ($key === UserDataFilter::SORT_FIELD_USR_LASTNAME):
                    $orders[] = 'usr_data.lastname' . $direction;
                    break;

                case ($key === UserDataFilter::SORT_FIELD_OBJ_TITLE):
                    $orders[] = 'cert.title' . $direction;
                    break;

                case ($key === UserDataFilter::SORT_FIELD_ISSUE_TIMESTAMP):
                    $orders[] = 'cert.acquired_timestamp' . $direction;
                    break;

                default:
                    break;
            }
        }

        $orderBy = 'ORDER BY ' . implode(', ', $orders);

        return $orderBy;
    }

    /**
     * Creating the additional where condition based on the filter object
     * @param UserDataFilter $filter
     * @return string
     */
    private function createWhereCondition(UserDataFilter $filter) : string
    {
        $wheres = [];

        $userIds = $filter->getUserIds();
        if (!empty($userIds)) {
            $wheres[] = $this->database->in('cert.user_id', $userIds, false, ilDBConstants::T_INTEGER);
        }

        $objIds = $filter->getObjIds();
        if (!empty($objIds)) {
            $wheres[] = $this->database->in('cert.obj_id', $objIds, false, ilDBConstants::T_INTEGER);
        }

        $firstName = $filter->getUserFirstName();
        if (!empty($firstName)) {
            $wheres[] = $this->database->like('usr_data.firstname', ilDBConstants::T_TEXT, '%' . $firstName . '%');
        }

        $lastName = $filter->getUserLastName();
        if (!empty($lastName)) {
            $wheres[] = $this->database->like('usr_data.lastname', ilDBConstants::T_TEXT, '%' . $lastName . '%');
        }

        $login = $filter->getUserLogin();
        if (!empty($login)) {
            $wheres[] = $this->database->like('usr_data.login', ilDBConstants::T_TEXT, '%' . $login . '%');
        }

        $userEmail = $filter->getUserEmail();
        if (!empty($userEmail)) {
            $wheres[] = '(' . $this->database->like('usr_data.email', ilDBConstants::T_TEXT, '%' . $userEmail . '%')
                . ' OR ' . $this->database->like('usr_data.second_email', ilDBConstants::T_TEXT, '%' . $userEmail . '%')
                . ')';
        }

        $issuedBeforeTimestamp = $filter->getIssuedBeforeTimestamp();
        if ($issuedBeforeTimestamp !== null) {
            $wheres[] = 'cert.acquired_timestamp < ' . $this->database->quote(
                $issuedBeforeTimestamp,
                ilDBConstants::T_INTEGER
            );
        }

        $issuedAfterTimestamp = $filter->getIssuedAfterTimestamp();
        if ($issuedAfterTimestamp !== null) {
            $wheres[] = 'cert.acquired_timestamp > ' . $this->database->quote(
                $issuedAfterTimestamp,
                ilDBConstants::T_INTEGER
            );
        }

        $title = $filter->getObjectTitle();
        if (!empty($title)) {
            $wheres[] = $this->database->like('cert.title', ilDBConstants::T_TEXT, '%' . $title . '%');
        }

        $onlyCertActive = $filter->isOnlyCertActive();
        if ($onlyCertActive === true) {
            $wheres[] = 'cert.currently_active = ' . $this->database->quote(1, ilDBConstants::T_INTEGER);
        }

        if (empty($wheres)) {
            return '';
        }

        $sql = 'WHERE ' . implode(' AND ', $wheres);

        return $sql;
    }
}
