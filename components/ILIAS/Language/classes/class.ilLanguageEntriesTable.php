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

use ILIAS\Language\ComponentTranslation\PluralFormKey;
use ILIAS\UI\Component\Table as DataTable;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * Read-only KS Data Table of the entries of one language, KS has no editable column type.
 * Editing happens in the Round-Trip modal of ilObjLanguageExtGUI::buildEditEntriesModal().
 */
class ilLanguageEntriesTable implements DataTable\DataRetrieval
{
    /** Preset of the module filter, also used while the filter is deactivated. */
    public const string DEFAULT_MODULE = 'administration';

    /** Per-request caches, otherwise each table render repeats the same queries and file reads. */
    private ?array $filtered_entries_cache = null;
    private ?array $conflicts_check_cache = null;
    private ?array $remarks_cache = null;
    private ?array $effective_remarks_cache = null;
    private ?array $compare_content_cache = null;

    public function __construct(
        protected ilObjLanguageExt $object,
        protected ilLanguage $lng,
        protected ILIAS\UI\Factory $ui_factory,
        protected URLBuilder $url_builder,
        protected URLBuilderToken $action_token,
        protected URLBuilderToken $row_id_token,
        protected bool $langmode
    ) {
    }

    /** Also the filter id and the view-control session key, separate for page translation and administration. */
    public static function tableId(): string
    {
        return 'lang_ext_entries_' . (ilObjLanguageAccess::_isPageTranslation() ? 'trans' : 'admin');
    }

    public function getTable(mixed $filter_data): DataTable\Data
    {
        return $this->ui_factory->table()->data(
            $this,
            '',
            $this->getColumns($filter_data)
        )->withId(self::tableId())
            ->withActions([
                'edit' => $this->ui_factory->table()->action()->standard(
                    $this->lng->txt('edit'),
                    $this->url_builder->withParameter($this->action_token, 'edit'),
                    $this->row_id_token
                )->withAsync(),
            ]);
    }

    /** "default" is titled after the compare language, as in the former ilLanguageExtTableGUI. */
    protected function getColumns(mixed $filter_data): array
    {
        $f = $this->ui_factory->table()->column();

        $columns = [
            'module' => $f->text(ucfirst($this->lng->txt('module'))),
            'topic' => $f->text(ucfirst($this->lng->txt('identifier'))),
            'translation' => $f->text($this->lng->txt('meta_l_' . $this->object->key)),
            'default' => $f->text($this->getCompareTitle($filter_data)),
        ];
        if (!$this->langmode) {
            return $columns;
        }

        return $columns + [
            'commented' => $f->boolean(
                $this->lng->txt('comment'),
                $this->ui_factory->symbol()->icon()->custom(
                    'assets/images/standard/icon_checked.svg',
                    $this->lng->txt('yes'),
                    'small'
                ),
                $this->ui_factory->symbol()->icon()->custom(
                    'assets/images/standard/icon_unchecked.svg',
                    $this->lng->txt('no'),
                    'small'
                )
            )->withIsSortable(false),
        ];
    }

    public function getCompareTitle(mixed $filter_data): string
    {
        $compare = $this->getCompareLanguage($filter_data);
        $title = $this->lng->txt('meta_l_' . $compare);
        if ($compare === $this->object->key) {
            $title .= ' ' . $this->lng->txt('language_default_entries');
        }

        return $title;
    }

    /**
     * The compare language's values for the current filter scope, as shown in the "default" column.
     *
     * @return array<string, string>
     */
    public function getCompareValues(mixed $filter_data): array
    {
        [$modules, $topics] = $this->resolveModulesAndTopics($filter_data);

        return $this->getCompareContent($filter_data, $modules, $topics);
    }

    private function getCompareLanguage(mixed $filter_data): string
    {
        return (string) $this->filterValue($filter_data, 'compare', $this->lng->getDefaultLanguage());
    }

    /**
     * Without pagination, also resolves the ALL_OBJECTS selection.
     *
     * @return list<string>
     */
    public function getFilteredEntryNames(mixed $filter_data): array
    {
        return array_keys($this->getFilteredEntries($filter_data));
    }

    /**
     * The message for the "conflicts" mode, set by the caller.
     *
     * @return array{type: string, text: string}|null
     */
    public function getConflictsNotice(mixed $filter_data): ?array
    {
        if ((string) $this->filterValue($filter_data, 'mode', 'all') !== 'conflicts') {
            return null;
        }

        $conflicts = $this->resolveConflictsCheck();

        return match ($conflicts['status']) {
            'missing' => [
                'type' => 'failure',
                'text' => sprintf($this->lng->txt('language_former_file_missing'), $conflicts['former_file'])
                    . '<br />' . $this->lng->txt('language_former_file_description'),
            ],
            'equal' => [
                'type' => 'info',
                'text' => sprintf($this->lng->txt('language_former_file_equal'), $conflicts['former_file'])
                    . '<br />' . $this->lng->txt('language_former_file_description'),
            ],
            default => null,
        };
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $entries = $this->getFilteredEntries($filter_data);

        [$modules, $topics] = $this->resolveModulesAndTopics($filter_data);
        $compare_content = $this->getCompareContent($filter_data, $modules, $topics);
        $names = $this->sortEntryNames(array_keys($entries), $entries, $compare_content, $order);
        $names = array_slice($names, $range->getStart(), $range->getLength());

        foreach ($names as $name) {
            $keys = explode($this->lng->separator, $name);

            // KS text columns are rendered unescaped
            $record = [
                'module' => $this->escape($keys[0] ?? ''),
                'topic' => $this->escape($keys[1] ?? ''),
                'translation' => $this->escape((string) ($entries[$name] ?? '')),
                'default' => $this->escape((string) ($compare_content[$name] ?? '')),
            ];
            if ($this->langmode) {
                $record['commented'] = $this->effectiveRemarkOf($name) !== '';
            }

            yield $row_builder->buildDataRow($name, $record);
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Natural, case-insensitive order on precomputed keys; DESC flips the comparator so ties keep their order.
     *
     * @param list<string> $names
     */
    private function sortEntryNames(array $names, array $entries, array $compare_content, Order $order): array
    {
        [$order_field, $order_direction] = $order->join([], static fn($ret, $key, $value): array => [$key, $value]);

        $sort_keys = [];
        foreach ($names as $name) {
            $keys = explode($this->lng->separator, $name);
            $sort_keys[$name] = match ($order_field) {
                'topic' => $keys[1] ?? '',
                'translation' => (string) ($entries[$name] ?? ''),
                'default' => (string) ($compare_content[$name] ?? ''),
                default => $keys[0] ?? '', // 'module' and any unknown field
            };
        }

        $direction = $order_direction === Order::DESC ? -1 : 1;
        uksort(
            $sort_keys,
            static fn(string $a, string $b): int => $direction * strnatcasecmp($sort_keys[$a], $sort_keys[$b])
        );

        // numeric-looking string keys become int array keys
        return array_map('strval', array_keys($sort_keys));
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getFilteredEntryNames($filter_data));
    }

    /** module#:#topic => value for the current filter. */
    private function getFilteredEntries(mixed $filter_data): array
    {
        if ($this->filtered_entries_cache !== null) {
            return $this->filtered_entries_cache;
        }

        return $this->filtered_entries_cache = $this->filterByComment(
            $this->filterByIdentifier($this->computeFilteredEntries($filter_data), $filter_data),
            $filter_data
        );
    }

    /**
     * Case-insensitive substring match on the remark shown and edited in langmode, see effectiveRemarkOf().
     *
     * @param array<string, string> $entries
     * @return array<string, string>
     */
    private function filterByComment(array $entries, mixed $filter_data): array
    {
        if (!$this->langmode || ilObjLanguageAccess::_isPageTranslation()) {
            return $entries;
        }

        $needle = trim((string) $this->filterValue($filter_data, 'comment', ''));
        if ($needle === '') {
            return $entries;
        }

        return array_filter(
            $entries,
            fn(int|string $name): bool => mb_stripos($this->effectiveRemarkOf((string) $name), $needle) !== false,
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Literal, ASCII-case-insensitive substring of the identifier, unlike the SQL LIKE of "pattern".
     *
     * @param array<string, string> $entries
     * @return array<string, string>
     */
    private function filterByIdentifier(array $entries, mixed $filter_data): array
    {
        if (ilObjLanguageAccess::_isPageTranslation()) {
            return $entries;
        }

        $needle = trim((string) $this->filterValue($filter_data, 'identifier', ''));
        if ($needle === '') {
            return $entries;
        }

        return array_filter(
            $entries,
            fn(string $name): bool => stripos(explode($this->lng->separator, $name)[1] ?? '', $needle) !== false,
            ARRAY_FILTER_USE_KEY
        );
    }

    private function computeFilteredEntries(mixed $filter_data): array
    {
        [$modules, $topics] = $this->resolveModulesAndTopics($filter_data);

        if (ilObjLanguageAccess::_isPageTranslation()) {
            return ilObjLanguageExt::_getValues($this->object->key, $modules, $topics);
        }

        $filter_mode = (string) $this->filterValue($filter_data, 'mode', 'all');
        $filter_pattern = (string) $this->filterValue($filter_data, 'pattern', '');

        switch ($filter_mode) {
            case 'changed':
                return $this->object->getChangedValues($modules, $filter_pattern, $topics);

            case 'added':   // langmode only
                return $this->object->getAddedValues($modules, $filter_pattern, $topics);

            case 'unchanged':
                return $this->object->getUnchangedValues($modules, $filter_pattern, $topics);

            case 'commented':
                return $this->object->getCommentedValues($modules, $filter_pattern, $topics);

            case 'dbremarks':
                $translations = $this->object->getAllValues($modules, $filter_pattern, $topics);
                // only local remarks, no shipped notes
                $local_remarks = $this->getRemarks();
                return array_filter(
                    $translations,
                    fn(int|string $name): bool => $this->remarkOf($local_remarks, (string) $name) !== '',
                    ARRAY_FILTER_USE_KEY
                );

            case 'equal':
                $translations = $this->object->getAllValues($modules, $filter_pattern, $topics);
                return array_intersect_assoc(
                    $translations,
                    $this->getCompareContent($filter_data, $modules, $topics)
                );

            case 'different':
                $translations = $this->object->getAllValues($modules, $filter_pattern, $topics);
                return array_diff_assoc(
                    $translations,
                    $this->getCompareContent($filter_data, $modules, $topics)
                );

            case 'conflicts':
                $conflicts = $this->resolveConflictsCheck();
                if ($conflicts['status'] !== 'ok') {
                    return [];
                }
                $translations = $this->object->getChangedValues($modules, $filter_pattern, $topics);
                return array_intersect_key($translations, $conflicts['global_changes']);

            case 'all':
            default:
                return $this->object->getAllValues($modules, $filter_pattern, $topics);
        }
    }

    private function getRemarks(): array
    {
        if ($this->remarks_cache !== null) {
            return $this->remarks_cache;
        }

        return $this->remarks_cache = $this->object->getAllRemarks();
    }

    /** The remark the edit form shows and saveValues() compares against, see ilObjLanguageExt::getEffectiveRemarks(). */
    public function effectiveRemarkOf(string $name): string
    {
        $this->effective_remarks_cache ??= $this->object->getEffectiveRemarks();

        return $this->remarkOf($this->effective_remarks_cache, $name);
    }

    /**
     * A form of a plural message without a comment of its own gets the one of its identifier (see PluralFormKey).
     *
     * @param array<string, string|null> $comments
     */
    private function remarkOf(array $comments, string $name): string
    {
        if (($comments[$name] ?? '') !== '') {
            return (string) $comments[$name];
        }
        $keys = explode($this->lng->separator, $name, 2);
        $identifier = PluralFormKey::parse($keys[1] ?? '')[0] ?? null;

        return $identifier === null ? '' : (string) ($comments[$keys[0] . $this->lng->separator . $identifier] ?? '');
    }

    /** Shipped values (global file, .po of PO modules) when compared with the edited language itself, else the DB values. */
    private function getCompareContent(mixed $filter_data, array $modules, array $topics): array
    {
        if ($this->compare_content_cache !== null) {
            return $this->compare_content_cache;
        }

        $compare = $this->getCompareLanguage($filter_data);

        if ($compare === $this->object->key) {
            return $this->compare_content_cache = $this->object->getShippedValues();
        }

        return $this->compare_content_cache = ilObjLanguageExt::_getValues($compare, $modules, $topics);
    }

    /**
     * Page translation uses the modules/topics saved in the session instead of the filter fields.
     * Otherwise topics stay empty, filterByIdentifier() applies "identifier" afterwards.
     *
     * @return array{0: list<string>, 1: list<string>}
     */
    private function resolveModulesAndTopics(mixed $filter_data): array
    {
        if (ilObjLanguageAccess::_isPageTranslation()) {
            return [ilObjLanguageAccess::_getSavedModules(), ilObjLanguageAccess::_getSavedTopics()];
        }

        $filter_module = (string) $this->filterValue($filter_data, 'module', self::DEFAULT_MODULE);
        $filter_module = $filter_module === 'all' ? '' : $filter_module;
        $filter_modules = $filter_module !== '' ? [$filter_module] : [];

        return [$filter_modules, []];
    }

    /** @return array{status: 'missing'|'equal'|'ok', former_file: string, global_changes?: array} */
    private function resolveConflictsCheck(): array
    {
        if ($this->conflicts_check_cache !== null) {
            return $this->conflicts_check_cache;
        }

        return $this->conflicts_check_cache = $this->computeConflictsCheck();
    }

    /**
     * @return array{status: 'missing'|'equal'|'ok', former_file: string, global_changes?: array}
     */
    private function computeConflictsCheck(): array
    {
        $former_file = $this->object->getDataPath() . '/ilias_' . $this->object->key . '.lang';

        if (!is_readable($former_file)) {
            return ['status' => 'missing', 'former_file' => $former_file];
        }

        $former_file_obj = new ilLanguageFile($former_file);
        $former_file_obj->read();
        // PO modules are compared with the backup of their shipped .po (written by "save_dist")
        $global_changes = $this->object->getShippedChangesSinceBackup($former_file_obj, $this->object->getDataPath());

        if (count($global_changes) === 0) {
            return ['status' => 'equal', 'former_file' => $former_file];
        }

        return ['status' => 'ok', 'former_file' => $former_file, 'global_changes' => $global_changes];
    }

    /** $filter_data is null for a deactivated filter, then every field falls back to $default. */
    private function filterValue(mixed $filter_data, string $key, mixed $default = null): mixed
    {
        if (!is_array($filter_data) || !isset($filter_data[$key])) {
            return $default;
        }

        $value = $filter_data[$key]->getValue();

        return ($value === null || $value === '') ? $default : $value;
    }
}
