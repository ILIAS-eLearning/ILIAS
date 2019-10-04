<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace Certificate\API\Repository;

use Certificate\API\Data\UserCertificateDto;
use Certificate\API\Filter\UserDataFilter;
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
        $userIds = $filter->getUserIds();

        $sql = '
SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.user_id,
  il_cert_user_cert.user_name,
  il_cert_user_cert.acquired_timestamp,
  il_cert_user_cert.currently_active,
  il_cert_user_cert.id,
  (CASE WHEN (object_data.title IS NULL)
    THEN
      CASE WHEN (object_data_del.title IS NULL)
        THEN ' . $this->database->quote($this->defaultTitle, 'text') . '
        ELSE object_data_del.title
        END
    ELSE object_data.title 
    END
  ) as title,
  object_reference.ref_id,
  usr_data.firstname,
  usr_data.lastname,
  usr_data.email,
  usr_data.login,
  usr_data.second_email
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_reference ON object_reference.obj_id = il_cert_user_cert.obj_id
INNER JOIN usr_data ON usr_data.usr_id = il_cert_user_cert.user_id
WHERE ' . $this->database->in('il_cert_user_cert.user_id', $userIds, false, 'integer')
            . $this->createWhereCondition($filter) . ' ' . $this->createOrderByClause($filter);

        $query = $this->database->query($sql);

        $result = array();
        while ($row = $this->database->fetchAssoc($query)) {
            $id = (int) $row['id'];

            if (isset($result[$id])) {
                $result[$id]->addRefId((int) $row['ref_id']);
                continue;
            }

            $link = '';
            if (array() !== $ilCtrlStack) {
                $ilCtrlStack[] = ilUserCertificateApiGUI::class;
                $this->controller->setParameterByClass(ilUserCertificateApiGUI::class, 'certificate_id', $id);
                $link = $this->controller->getLinkTargetByClass($ilCtrlStack, ilUserCertificateApiGUI::CMD_DOWNLOAD);
                $this->controller->clearParametersByClass(ilUserCertificateApiGUI::class);
            }

            $dataObject = new UserCertificateDto(
                $id,
                $row['title'],
                (int) $row['obj_id'],
                (int) $row['acquired_timestamp'],
                (int) $row['user_id'],
                $row['firstname'],
                $row['lastname'],
                $row['login'],
                $row['email'],
                $row['second_email'],
                [(int) $row['ref_id']],
                $link
            );

            $result[$id] = $dataObject;
        }
        return $result;
    }

    /**
     * @param UserDataFilter $filter
     * @return string
     */
    private function createOrderByClause(UserDataFilter $filter) : string
    {
        $sorts = $filter->getSorts();

        if (!empty($sorts)) {
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
                    $orders[] = 'title' . $direction;
                    break;

                case ($key === UserDataFilter::SORT_FIELD_ISSUE_TIMESTAMP):
                    $orders[] = 'il_cert_user_cert.acquired_timestamp' . $direction;
                    break;

                default:
                    break;
            }
        }

        $orderBy = ' ORDER BY ' . implode(', ', $orders);

        return $orderBy;
    }

    /**
     * Creating the additional where condition based on the filter object
     * @param UserDataFilter $filter
     * @return string
     */
    private function createWhereCondition(UserDataFilter $filter) : string
    {
        $sql = '';

        $firstName = $filter->getUserFirstName();
        if (null !== $firstName) {
            $sql .= ' AND ' . $this->database->like('  usr_data.firstname', 'text', '%' . $firstName . '%');
        }

        $lastName = $filter->getUserLastName();
        if (null !== $lastName) {
            $sql .= ' AND ' . $this->database->like('usr_data.lastname', 'text', '%' . $lastName . '%');
        }

        $login = $filter->getUserLogin();
        if (null !== $login) {
            $sql .= ' AND ' . $this->database->like('  usr_data.login', 'text', '%' . $login . '%');
        }

        $userEmail = $filter->getUserEmail();
        if (null !== $userEmail) {
            $sql .= ' AND ( ' . $this->database->like('usr_data.email', 'text', '%' . $userEmail . '%');
            $sql .= ' OR ' . $this->database->like('usr_data.second_email', 'text', '%' . $userEmail . '%');
            $sql .= ')';

        }

        $issuedBeforeTimestamp = $filter->getIssuedBeforeTimestamp();
        if (null !== $issuedBeforeTimestamp) {
            $sql .= ' AND il_cert_user_cert.acquired_timestamp < ' . $this->database->quote($issuedBeforeTimestamp,
                    'integer');
        }

        $issuedAfterTimestamp = $filter->getIssuedAfterTimestamp();
        if (null !== $issuedAfterTimestamp) {
            $sql .= ' AND il_cert_user_cert.acquired_timestamp > ' . $this->database->quote($issuedAfterTimestamp,
                    'integer');
        }

        $objectId = $filter->getObjectId();
        if (null !== $objectId) {
            $sql .= ' AND   il_cert_user_cert.obj_id = ' . $this->database->quote($objectId, 'integer');
        }

        $title = $filter->getObjectTitle();
        if (null !== $title) {
            $sql .= ' AND (' . $this->database->like('object_data.title', 'text', '%' . $title . '%');
            $sql .= ' OR ' . $this->database->like('object_data_del.title', 'text', '%' . $title . '%') . ')';
        }

        $onlyActive = $filter->isOnlyActive();
        if (true === $onlyActive) {
            $sql .= ' AND il_cert_user_cert.currently_active = ' . $this->database->quote(1, 'integer');
        }

        return $sql;
    }
}
