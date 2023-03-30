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
 ********************************************************************
 */
declare(strict_types=1);

namespace ILIAS\MyStaff\ListCertificates;

use ILIAS\Certificate\API\Data\UserCertificateDto;
use ILIAS\Certificate\API\Filter\UserDataFilter;
use ILIAS\Certificate\API\UserCertificateAPI;
use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilMStListCertificatesGUI;
use ilMyStaffGUI;
use ilOrgUnitOperation;

/**
 * Class ilMStListCertificates
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCertificates
{
    protected Container $dic;

    /**
     * ilMStListCertificates constructor.
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    /**
     * @return UserCertificateDto[]
     */
    final public function getData(array $options = array()): array
    {
        //Permission Filter
        $operation_access = ilOrgUnitOperation::OP_VIEW_CERTIFICATES;

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
            if (empty($users)) {
                continue;
            }
            $usr_data_filter = new UserDataFilter();
            $usr_data_filter = $usr_data_filter->withUserIds($users);
            $usr_data_filter = $usr_data_filter->withObjIds(ilMyStaffAccess::getInstance()->getIdsForUserAndOperation(
                $this->dic->user()->getId(),
                $operation_access
            ));

            if (!empty($options['filters']['user'])) {
                $usr_data_filter = $usr_data_filter->withUserIdentification($options['filters']['user']);
            }
            if (!empty($options['filters']['obj_title'])) {
                $usr_data_filter = $usr_data_filter->withObjectTitle($options['filters']['obj_title']);
            }
            if (!empty($options['filters']['org_unit'])) {
                $org_unit_id = (int) $options['filters']['org_unit'];
                $usr_data_filter = $usr_data_filter->withOrgUnitIds([$org_unit_id]);
            }

            if (!empty($options['sort']['field']) && !empty($options['sort']['direction'])) {
                if ($options['sort']['field'] === "objectTitle" && $options['sort']['direction'] === "asc") {
                    $usr_data_filter = $usr_data_filter->withSortedObjectTitles(UserDataFilter::SORT_DIRECTION_ASC);
                } elseif ($options['sort']['field'] === "objectTitle" && $options['sort']['direction'] === "desc") {
                    $usr_data_filter = $usr_data_filter->withSortedObjectTitles(UserDataFilter::SORT_DIRECTION_DESC);
                } elseif ($options['sort']['field'] === "issuedOnTimestamp" && $options['sort']['direction'] === "asc") {
                    $usr_data_filter = $usr_data_filter->withSortedIssuedOnTimestamps(UserDataFilter::SORT_DIRECTION_ASC);
                } elseif ($options['sort']['field'] === "issuedOnTimestamp" && $options['sort']['direction'] === "desc") {
                    $usr_data_filter = $usr_data_filter->withSortedIssuedOnTimestamps(UserDataFilter::SORT_DIRECTION_DESC);
                } elseif ($options['sort']['field'] === "userLogin" && $options['sort']['direction'] === "asc") {
                    $usr_data_filter = $usr_data_filter->withSortedLogins(UserDataFilter::SORT_DIRECTION_ASC);
                } elseif ($options['sort']['field'] === "userLogin" && $options['sort']['direction'] === "desc") {
                    $usr_data_filter = $usr_data_filter->withSortedLogins(UserDataFilter::SORT_DIRECTION_DESC);
                } elseif ($options['sort']['field'] === "userFirstName" && $options['sort']['direction'] === "asc") {
                    $usr_data_filter = $usr_data_filter->withSortedFirstNames(UserDataFilter::SORT_DIRECTION_ASC);
                } elseif ($options['sort']['field'] === "userFirstName" && $options['sort']['direction'] === "desc") {
                    $usr_data_filter = $usr_data_filter->withSortedFirstNames(UserDataFilter::SORT_DIRECTION_DESC);
                } elseif ($options['sort']['field'] === "userLastName" && $options['sort']['direction'] === "asc") {
                    $usr_data_filter = $usr_data_filter->withSortedLastNames(UserDataFilter::SORT_DIRECTION_ASC);
                } elseif ($options['sort']['field'] === "userLastName" && $options['sort']['direction'] === "desc") {
                    $usr_data_filter = $usr_data_filter->withSortedLastNames(UserDataFilter::SORT_DIRECTION_DESC);
                } elseif ($options['sort']['field'] === "userEmail" && $options['sort']['direction'] === "asc") {
                    $usr_data_filter = $usr_data_filter->withSortedEmails(UserDataFilter::SORT_DIRECTION_ASC);
                } elseif ($options['sort']['field'] === "userEmail" && $options['sort']['direction'] === "desc") {
                    $usr_data_filter = $usr_data_filter->withSortedEmails(UserDataFilter::SORT_DIRECTION_DESC);
                }
            }

            if ((!empty($options['limit']['start']) || $options['limit']['start'] === 0)
                && !empty($options['limit']['end'])
            ) {
                $usr_data_filter = $usr_data_filter->withLimitOffset((int) $options['limit']['start']);
                $usr_data_filter = $usr_data_filter->withLimitCount((int) $options['limit']['end']);
            }

            $data = array_merge($data, $cert_api->getUserCertificateData(
                $usr_data_filter,
                [ilMyStaffGUI::class, ilMStListCertificatesGUI::class]
            ));
        }

        $unique_cert_data = [];
        foreach ($data as $cert_data) {
            assert($cert_data instanceof UserCertificateDto);
            $unique_cert_data[$cert_data->getCertificateId()] = $cert_data;
        }

        return $unique_cert_data;
    }
}
