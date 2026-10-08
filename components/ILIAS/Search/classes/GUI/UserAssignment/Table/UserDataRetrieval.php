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

declare(strict_types=1);

namespace ILIAS\Search\GUI\UserAssignment\Table;

use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use Generator;
use ILIAS\UI\Component\Table\DataRetrieval as DataRetrievalInterface;
use ilSearchSettings;
use ilUserQuery;
use ilLanguage;
use ilDatePresentation;
use ilDateTime;
use ilDate;
use ILIAS\User\Profile\Profile;
use ilOrgUnitPathStorage;
use ILIAS\UI\Factory as UIFactory;
use ilCtrlInterface;
use ILIAS\UI\Component\Link\Link;
use ilAdministrationGUI;
use ilObjUserGUI;

use function ILIAS\UI\examples\Symbol\Glyph\Login\login;

class UserDataRetrieval implements DataRetrievalInterface
{
    protected array $user_data;

    /**
     * @param int[] $user_ids
     */
    public function __construct(
        protected bool $admin_mode,
        protected bool $user_limitations,
        protected array $user_ids,
        protected ilLanguage $lng,
        protected Profile $profile,
        protected UIFactory $ui_factory,
        protected ilCtrlInterface $ctrl
    ) {
    }

    protected function getUserData(array $visible_column_ids): array
    {
        if (isset($this->user_data)) {
            return $this->user_data;
        }

        if (!$this->user_ids) {
            return $this->user_data = [];
        }

        $usr_data_fields = [];
        foreach ($visible_column_ids as $field) {
            if ($field === 'org_units' || $field === 'access_until') {
                continue;
            }
            if (substr($field, 0, 3) == 'udf') {
                continue;
            }
            $usr_data_fields[] = $field;
        }

        $u_query = new ilUserQuery();
        $u_query->setOrderField('login');
        $u_query->setOrderDirection('ASC');
        $u_query->setLimit(999999);

        if (!ilSearchSettings::getInstance()->isInactiveUserVisible() && $this->user_limitations) {
            $u_query->setActionFilter('active');
        }

        if (!ilSearchSettings::getInstance()->isLimitedUserVisible() && $this->user_limitations) {
            $u_query->setAccessFilter(true);
        }

        $u_query->setAdditionalFields($usr_data_fields);
        $u_query->setUserFilter($this->user_ids);

        $usr_data = $u_query->query();

        return $this->user_data = $usr_data['set'];
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters // all selectable columns
    ): Generator {
        $data = $this->getUserData($additional_parameters);

        $udf_ids = [];
        foreach ($visible_column_ids as $field) {
            if (!substr($field, 0, 3) == 'udf') {
                continue;
            }
            $udf_ids[] = substr($field, 4);
        }

        // Custom user data fields
        if ($udf_ids) {
            foreach ($data as $k => $set) {
                if ((int) ($set['usr_id'] ?? 0) === 0) {
                    continue;
                }
                $profile_data = $this->profile->getDataFor((int) $set['usr_id']);
                foreach ($udf_ids as $udf_field) {
                    $data[$k]['udf_' . $udf_field] = implode(
                        ', ',
                        $profile_data->getAdditionalFieldByIdentifier($udf_field) ?? []
                    );
                }
            }
        }

        if ($this->admin_mode && in_array('access_until', $visible_column_ids)) {
            // see ilUserTableGUI
            $current_time = time();
            foreach ($data as $k => $user) {
                if ($user['active']) {
                    if ($user['time_limit_unlimited']) {
                        $txt_access = $this->lng->txt('access_unlimited');
                    } elseif ($user['time_limit_until'] < $current_time) {
                        $txt_access = $this->lng->txt('access_expired');
                    } else {
                        $txt_access = ilDatePresentation::formatDate(new ilDateTime($user['time_limit_until'], IL_CAL_UNIX));
                    }
                } else {
                    $txt_access = $this->lng->txt('inactive');
                }
                $data[$k]['access_until'] = $txt_access;
            }
        }

        $order_field = array_keys($order->get())[0];
        $order_direction = $order->get()[$order_field];
        uasort($data, fn($a, $b) => strtolower((string) $a[$order_field]) <=> strtolower((string) $b[$order_field]));
        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }
        $data = array_slice($data, $range->getStart(), $range->getLength(), true);

        // final formatting
        $row_data = [];
        foreach ($data as $set) {
            $datum = [];
            foreach ($visible_column_ids as $field) {
                $datum[$field] = match ($field) {
                    'gender' => $set['gender'] ? $this->lng->txt('gender_' . $set['gender']) : '',
                    'birthday' => $set['birthday'] ?
                        ilDatePresentation::formatDate(new ilDate($set['birthday'], IL_CAL_DATE)) :
                        $this->lng->txt('no_date'),
                    'last_login' => $set['last_login'] ?
                        ilDatePresentation::formatDate(new ilDateTime($set['last_login'], IL_CAL_DATETIME)) :
                        $this->lng->txt('no_date'),
                    'org_units' => ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits((int) $set['usr_id']),
                    'login' => $this->admin_mode ? $this->buildUserLink($set['usr_id'], $set['login']) : $set['login'],
                    default => is_array($set[$field] ?? null) ? implode(', ', $set[$field]) : ($set[$field] ?? '')
                };
            }
            $row_data[$set['usr_id']] = $datum;
        }

        foreach ($row_data as $id => $datum) {
            yield $row_builder->buildDataRow(
                (string) $id,
                $datum
            );
        }
    }

    /**
     * @return int[]
     */
    public function getAllIDs(): array
    {
        $usr_ids = [];
        foreach ($this->getUserData([]) as $user_data) {
            $usr_ids[] = $user_data['usr_id'];
        }
        return $usr_ids;
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters // all selectable columns
    ): ?int {
        return count($this->getUserData($additional_parameters));
    }

    protected function buildUserLink(int $user_id, string $login): Link
    {
        $this->ctrl->setParameterByClass(ilObjUserGUI::class, 'ref_id', '7');
        $this->ctrl->setParameterByClass(ilObjUserGUI::class, 'obj_id', $user_id);
        $this->ctrl->setParameterByClass(ilObjUserGUI::class, 'search', '1');
        $link = $this->ctrl->getLinkTargetByClass([ilAdministrationGUI::class, ilObjUserGUI::class], 'view');
        $this->ctrl->clearParameterByClass(ilObjUserGUI::class, 'ref_id');
        $this->ctrl->clearParameterByClass(ilObjUserGUI::class, 'obj_id');
        $this->ctrl->clearParameterByClass(ilObjUserGUI::class, 'search');
        return $this->ui_factory->link()->standard($login, $link);
    }
}
