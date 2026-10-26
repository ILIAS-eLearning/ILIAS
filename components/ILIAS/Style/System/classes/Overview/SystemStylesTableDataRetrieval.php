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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;

class SystemStylesTableDataRetrieval implements DataRetrieval
{
    /** @var array<string, array{
     * style_title: string,
     * skin_title: string,
     * substyle_of: string,
     * scope: string,
     * version: string,
     * default?: bool,
     * status?: string,
     * users?: int|string
     * }>|null */
    private ?array $row_data = null;

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly Renderer $renderer,
    ) {
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $rows = array_slice($this->sortRecords($this->getRowData(), $order), $range->getStart(), $range->getLength(), true);
        foreach ($rows as $id => $row) {
            yield $this->applyRowActions($row_builder->buildDataRow($id, $row), $id, $row);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getRowData());
    }

    /**
     * @return array<string, array{
     *      style_title: string,
     *      skin_title: string,
     *      substyle_of: string,
     *      scope: string,
     *      version: string,
     *      default?: bool,
     *      status?: string,
     *      users?: int|string
     *  }>
     */
    private function getRowData(): array
    {
        if ($this->row_data === null) {
            $this->row_data = $this->buildRowData();
        }

        return $this->row_data;
    }

    /**
     * @return array<string, array{
     *      style_title: string,
     *      skin_title: string,
     *      substyle_of: string,
     *      scope: string,
     *      version: string,
     *      default?: bool,
     *      status?: string,
     *      users?: int|string
     *  }>
     */
    private function buildRowData(): array
    {
        $rows = [];

        foreach ($this->getStyles() as $id => $style) {
            $is_substyle = $style['substyle_of'] !== '';
            $is_other = $id === 'other';

            $row = [
                SystemStylesTable::COL_STYLE_TITLE => $style['style_name'],
                SystemStylesTable::COL_SKIN_TITLE => $style['skin_name'],
                SystemStylesTable::COL_SUBSTYLE_OF => $style['substyle_of_name'],
                SystemStylesTable::COL_SCOPE => $this->buildScope($style, $is_substyle),
                SystemStylesTable::COL_VERSION => $style['version'],
            ];

            if (!$is_substyle && !$is_other) {
                $row[SystemStylesTable::COL_DEFAULT] =
                    ilSystemStyleSettings::getCurrentDefaultSkin() === $style['skin_id']
                    && ilSystemStyleSettings::getCurrentDefaultStyle() === $style['style_id'];
            }

            if (!$is_other) {
                $status_style_id = $is_substyle ? $style['substyle_of'] : $style['style_id'];
                $is_active = ilSystemStyleSettings::_lookupActivatedStyle($style['skin_id'], $status_style_id);
                $row[SystemStylesTable::COL_STATUS] = $this->lng->txt($is_active ? 'active' : 'inactive');
            }
            $row[SystemStylesTable::COL_USERS] = $is_substyle ? '-' : (int) $style['users'];

            $rows[$id] = $row;
        }

        return $rows;
    }

    /**
     * @return array<string, array{
     *      title: string,
     *      id: string,
     *      template_id: string,
     *      template_name?: string,
     *      skin_id: string,
     *      style_id: string,
     *      skin_name: string,
     *      style_name: string,
     *      users: int,
     *      version: string,
     *      substyle_of: string,
     *      substyle_of_name: string
     *  }>
     */
    private function getStyles(): array
    {
        // output 'other' row for all users, that are not assigned to any existing style
        $users_missing_styles = 0;
        foreach (ilObjUser::_getAllUserAssignedStyles() as $skin_style_id) {
            $style_arr = explode(':', $skin_style_id);
            if (!isset($style_arr[1]) || !ilStyleDefinition::styleExists($style_arr[1])) {
                $users_missing_styles += ilObjUser::_getNumberOfUsersForStyle($style_arr[0], $style_arr[1] ?? '');
            }
        }

        $all_styles = ilStyleDefinition::getAllSkinStyles() ?? [];
        if ($users_missing_styles > 0) {
            $all_styles['other'] =
                [
                    'title' => $this->lng->txt('other'),
                    'id' => 'other',
                    'template_id' => '',
                    'skin_id' => 'other',
                    'style_id' => 'other',
                    'skin_name' => 'other',
                    'style_name' => 'other',
                    'users' => $users_missing_styles,
                    'version' => '-',
                    'substyle_of' => '',
                    'substyle_of_name' => '',
                ];
        }

        return $all_styles;
    }

    /**
     * @param array{
     *      style_title: string,
     *      skin_title: string,
     *      substyle_of: string,
     *      scope: string,
     *      version: string,
     *      default?: bool,
     *      status?: string,
     *      users?: int|string
     *  } $row
     */
    private function applyRowActions(DataRow $data_row, string $row_id, array $row): DataRow
    {
        $style = $this->styleById($row_id);
        $is_other = $row_id === 'other';
        $is_substyle = ($style['substyle_of'] ?? '') !== '';
        $is_parent = !$is_substyle && !$is_other;
        $is_default = ($row[SystemStylesTable::COL_DEFAULT] ?? false) === true;
        $is_active = !$is_other && ilSystemStyleSettings::_lookupActivatedStyle(
            $style['skin_id'] ?? '',
            $is_substyle ? ($style['substyle_of'] ?? '') : ($style['style_id'] ?? ''),
        );

        if (!$is_parent || $is_default || !$is_active) {
            $data_row = $data_row->withDisabledAction(SystemStylesTable::ACTION_SET_DEFAULT);
        }

        if (!$is_parent) {
            $data_row = $data_row->withDisabledAction(SystemStylesTable::ACTION_ACTIVATE);
            $data_row = $data_row->withDisabledAction(SystemStylesTable::ACTION_DEACTIVATE);
        } else {
            $data_row = $data_row->withDisabledAction(
                $is_active ? SystemStylesTable::ACTION_ACTIVATE : SystemStylesTable::ACTION_DEACTIVATE,
            );

            if (($row[SystemStylesTable::COL_USERS] ?? 0) > 0 || $is_default) {
                $data_row = $data_row->withDisabledAction(SystemStylesTable::ACTION_DEACTIVATE);
            }
        }

        if (!$is_parent || ($style['skin_id'] ?? '') === 'default') {
            $data_row = $data_row->withDisabledAction(SystemStylesTable::ACTION_EXPORT);
        }

        if ($is_substyle || $this->isAssignmentDisabled($row)) {
            $data_row = $data_row->withDisabledAction(SystemStylesTable::ACTION_ASSIGN_STYLE);
        }

        if (!$is_substyle) {
            $data_row = $data_row->withDisabledAction(SystemStylesTable::ACTION_ASSIGN_CATEGORIES);
        }

        return $data_row;
    }

    /**
     * @return array{
     *      title: string,
     *      id: string,
     *      template_id: string,
     *      template_name?: string,
     *      skin_id: string,
     *      style_id: string,
     *      skin_name: string,
     *      style_name: string,
     *      users: int,
     *      version: string,
     *      substyle_of: string,
     *      substyle_of_name: string
     *  }|array{}
     */
    private function styleById(string $row_id): array
    {
        if ($row_id === 'other') {
            return [];
        }

        return (ilStyleDefinition::getAllSkinStyles() ?? [])[$row_id] ?? [];
    }

    /**
     * @param array{
     *      style_title: string,
     *      skin_title: string,
     *      substyle_of: string,
     *      scope: string,
     *      version: string,
     *      default?: bool,
     *      status?: string,
     *      users?: int|string
     *  } $row
     */
    private function isAssignmentDisabled(array $row): bool
    {
        $style_name = $row[SystemStylesTable::COL_STYLE_TITLE] ?? '';

        return array_all(
            ilStyleDefinition::getAllSkinStyles() ?? [],
            static fn(array $skin_style): bool => ($skin_style['substyle_of'] ?? '') !== ''
                || $style_name === ($skin_style['style_name'] ?? ''),
        );
    }

    /**
     * @param array{
     *      title: string,
     *      id: string,
     *      template_id: string,
     *      template_name?: string,
     *      skin_id: string,
     *      style_id: string,
     *      skin_name: string,
     *      style_name: string,
     *      users: int,
     *      version: string,
     *      substyle_of: string,
     *      substyle_of_name: string
     *  } $style
     */
    private function buildScope(array $style, bool $is_substyle): string
    {
        if (!$is_substyle) {
            return $this->lng->txt('global');
        }

        $categories = [];
        foreach (ilSystemStyleSettings::getSubStyleCategoryAssignments(
            $style['skin_id'],
            $style['substyle_of'],
            $style['style_id'],
        ) as $assignment) {
            $category_title = ilObject::_lookupTitle(ilObject::_lookupObjId((int) $assignment['ref_id']));
            if ($category_title) {
                $categories[] = $category_title;
            }
        }

        return $this->lng->txt('local') . $this->renderer->render($this->ui_factory->listing()->unordered($categories));
    }

    /**
     * @param array<string, array{
     *      style_title: string,
     *      skin_title: string,
     *      substyle_of: string,
     *      scope: string,
     *      version: string,
     *      default?: bool,
     *      status?: string,
     *      users?: int|string
     *  }> $records
     * @return array<string, array{
     *      style_title: string,
     *      skin_title: string,
     *      substyle_of: string,
     *      scope: string,
     *      version: string,
     *      default?: bool,
     *      status?: string,
     *      users?: int|string
     *  }>
     */
    private function sortRecords(array $records, Order $order): array
    {
        $sort_by = $order->get();
        if ($sort_by === []) {
            return $records;
        }

        uasort(
            $records,
            function (array $left, array $right) use ($sort_by): int {
                foreach ($sort_by as $field => $direction) {
                    $result = $this->compareValues($left[$field] ?? null, $right[$field] ?? null);
                    if ($result === 0) {
                        continue;
                    }

                    return $direction === Order::DESC ? -$result : $result;
                }

                return 0;
            }
        );

        return $records;
    }

    private function compareValues(mixed $left, mixed $right): int
    {
        if ($left === $right || is_object($left) || is_object($right)) {
            return 0;
        }

        if ($left === null) {
            return -1;
        }

        if ($right === null) {
            return 1;
        }

        if (is_numeric($left) && is_numeric($right)) {
            return $left <=> $right;
        }

        return strnatcasecmp((string) $left, (string) $right);
    }
}
