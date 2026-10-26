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

use ILIAS\Repository\Filter\FilterAdapterGUI;
use ILIAS\Repository\Table\TableAdapterGUI;

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup components\ILIASScormAicc
 */
class ilSCORMTrackingUsersTableGUI
{
    private int $obj_id;
    private object $parent_obj;
    private string $parent_cmd;
    private ilLanguage $lng;
    private ?FilterAdapterGUI $filter = null;
    private ?TableAdapterGUI $table = null;

    public function __construct(int $a_obj_id, ?object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;
        if ($a_parent_obj === null) {
            throw new ilException('Missing parent object');
        }

        $this->obj_id = $a_obj_id;
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        $this->lng = $DIC->language();
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    /**
     * Parse table content
     * @throws ilDateTimeException
     */
    public function parse(): void
    {
        global $DIC;
        $filters = $this->getTrackedUserFilters();
        $ui_factory = $DIC->ui()->factory();

        $users = $this->parent_obj->getObject()->getTrackedUsers($filters);
        $attempts = $this->parent_obj->getObject()->getAttemptsForUsers();
        $versions = $this->parent_obj->getObject()->getModuleVersionForUsers();

        $data = [];
        foreach ($users as $user) {
            $tmp = [];
            $tmp['id'] = (int) $user['user_id'];
            $tmp['user'] = (int) $user['user_id'];
            $tmp['name'] = ($user['lastname'] ?? '') . ', ' . ($user['firstname'] ?? '');
            $tmp['login'] = (string) ($user['login'] ?? '');
            $tmp['email'] = (string) ($user['email'] ?? '');
            $dt = new ilDateTime($user['last_access'], IL_CAL_DATETIME);
            $tmp['last_access'] = $dt->get(IL_CAL_UNIX);
            $tmp['attempts'] = (int) ($attempts[$user['user_id']] ?? 0);
            $tmp['version'] = (int) ($versions[$user['user_id']] ?? 0);

            $data[] = $tmp;
        }

        $this->table = (new TableAdapterGUI(
            'sco_tr_usrs_' . $this->obj_id,
            '',
            new ilSCORMTrackingTableRetrieval(
                $data,
                ['id', 'user', 'last_access', 'attempts', 'version']
            ),
            $this->parent_obj,
            $this->parent_cmd,
            '',
            '',
            null,
            static function (array $row): array {
                $row['last_access'] = ilDatePresentation::formatDate(
                    new ilDateTime((string) $row['last_access'], IL_CAL_UNIX)
                );
                return $row;
            },
            false
        ))
            ->filterData($filters)
            ->column('login', $ui_factory->table()->column()->text($this->lng->txt('login'))->withIsSortable(true)->withIsOptional(true, false))
            ->textColumn('name', $this->lng->txt('user'), true)
            ->column('email', $ui_factory->table()->column()->text($this->lng->txt('email'))->withIsSortable(true)->withIsOptional(true, false))
            ->textColumn('last_access', $this->lng->txt('last_access'), true)
            ->textColumn('attempts', $this->lng->txt('attempts'), true)
            ->textColumn('version', $this->lng->txt('version'), true)
            ->multiAction('deleteTrackingForUser', $this->lng->txt('delete'))
            ->multiAction('exportSelectionUsers', $this->lng->txt('export'));
    }

    /**
     * @return array<string, string>
     */
    private function getTrackedUserFilters(): array
    {
        $filter_data = $this->getFilter()->getData() ?? [];
        return [
            'lastname_filter' => trim((string) ($filter_data['lastname_filter'] ?? '')),
            'firstname_filter' => trim((string) ($filter_data['firstname_filter'] ?? '')),
            'login_filter' => trim((string) ($filter_data['login_filter'] ?? '')),
            'email_filter' => trim((string) ($filter_data['email_filter'] ?? '')),
        ];
    }

    private function getFilter(): FilterAdapterGUI
    {
        if ($this->filter === null) {
            $this->filter = new FilterAdapterGUI(
                'sco_tr_usrs_' . $this->obj_id,
                [get_class($this->parent_obj)],
                $this->parent_cmd
            );
            $this->filter->text('login_filter', $this->lng->txt('login'));
            $this->filter->text('firstname_filter', $this->lng->txt('firstname'));
            $this->filter->text('lastname_filter', $this->lng->txt('lastname'));
            $this->filter->text('email_filter', $this->lng->txt('email'));
        }
        return $this->filter;
    }

    public function handleCommand(): bool
    {
        return $this->table?->handleCommand() ?? false;
    }

    public function getFilterHTML(): string
    {
        return $this->getFilter()->render();
    }

    public function getTableHTML(): string
    {
        return $this->table?->render() ?? '';
    }

    public function getHTML(): string
    {
        return $this->getFilterHTML() . $this->getTableHTML();
    }
}
