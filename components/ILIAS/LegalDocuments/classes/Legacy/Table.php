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

namespace ILIAS\LegalDocuments\Legacy;

use ilYuiUtil;
use iljQueryUtil;
use Closure;
use ILIAS\LegalDocuments\Table as TableInterface;
use ILIAS\LegalDocuments\TableSelection;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;
use ilTable2GUI;
use ILIAS\LegalDocuments\SmoothTableConfig;
use ILIAS\LegalDocuments\TableFilter;
use InvalidArgumentException;

class Table extends ilTable2GUI implements TableSelection
{
    /** @var array<string, list<string>> */
    private readonly array $columns;
    /** @var list<string> */
    private array $sel = [];

    public function __construct(?object $gui, string $command, TableInterface $table)
    {
        global $DIC;
        $apply = fn($proc) => fn(array $args) => $proc(...$args);
        $translate = fn(string $txt, ...$args) => [$txt, ...$args];

        $id = substr(md5($table->name()), 0, 30);
        $this->setId($id);
        $this->columns = array_map($apply($translate), $table->columns());
        $this->setFormName($id);
        $config = new SmoothTableConfig($this);
        $table->config($config);
        parent::__construct($gui, $command);
        $config->flush();
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $command));
        $this->setRowTemplate('legacy-table-row.html', 'Services/LegalDocuments');
        array_map($apply($this->addColumn(...)), $this->visibleColumns());
        $this->setShowRowsSelector(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        iljQueryUtil::initjQuery($DIC->ui()->mainTemplate());
        ilYuiUtil::initPanel(false, $DIC->ui()->mainTemplate());
        ilYuiUtil::initOverlay($DIC->ui()->mainTemplate());
        $DIC->ui()->mainTemplate()->addJavaScript('./Services/Form/js/Form.js');
        $this->determineOffsetAndOrder();
        $this->setData($table->rows($this));
    }

    public function setSelectableColumns(...$names): void
    {
        $this->sel = array_merge($this->sel, $names);
    }

    public function selection(): array
    {
        return array_flip($this->sel);
    }

    public function getSelectableColumns(): array
    {
        return array_map(fn($x) => ['txt' => $x[0]], array_intersect_key(
            $this->columns,
            $this->selection()
        ));
    }

    public function filter(): array
    {
        return array_column(array_map(
            fn($input) => [
                $input->getPostVar(),
                $input->getValue()
            ],
            $this->filterInputs()
        ), 1, 0);
    }

    protected function isColumnVisible(int $index): bool
    {
        return true;
    }

    private function visibleColumns(): array
    {
        $restore_key_order = fn($array) => array_intersect_key($this->columns, $array);
        $base = array_diff_key($this->columns, $this->selection());

        return $restore_key_order(array_merge($base, array_intersect_key($this->columns, $this->getSelectedColumns())));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function resetParameters(array $parameters): void
    {
        $this->applyParamters(array_map(static fn(): string => '', $parameters));
    }

    /**
     * @param array<string, string> $parameters
     */
    private function applyParamters(array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            $this->ctrl->setParameter($this->getParentObject(), $key, $value);
        }
    }

    protected function fillRow(array $a_set): void
    {
        $this->requireKeys(array_keys($this->columns), $a_set);
        $set = array_intersect_key($a_set, $this->visibleColumns());
        $this->tpl->setVariable('VALUE', join('', array_map($this->tableCellOfField(...), $set)));
    }

    protected function tableCellOfField($x): string
    {
        return sprintf('<td>%s</td>', $this->asString($x));
    }

    /**
     * @param Component|list<Component>|Closure|string $x
     */
    private function asString($x): string
    {
        $is_component = fn($x): bool => $x instanceof Component;

        if ($is_component($x) || (is_array($x) && array_filter($x, fn($x) => !$is_component($x)) === [])) {
            return $GLOBALS['DIC']->ui()->renderer()->render($x);
        } elseif (is_callable($x)) {
            return $x();
        } elseif (is_string($x)) {
            return htmlentities($x);
        }

        throw new InvalidArgumentException('Value must be either: Component|list<Component>|Closure|string. Given: ' . var_export($x, true));
    }

    protected function txt(string $key): string
    {
        return $key === '' ? '' : $this->lng->txt($key);
    }

    /**
     * @param list<string> $required
     * @param array<string, mixed> $given
     */
    private function requireKeys(array $required, array $given): void
    {
        $given = array_keys($given);
        $missing = $this->intersect($required, $this->diff($required, $given));
        if ([] !== $missing) {
            throw new InvalidArgumentException('Missing keys: ' . join(', ', $missing));
        }
    }

    private function diff(array $a, array $b): array
    {
        return array_filter($a, fn($x) => !$this->has($x, $b));
    }

    private function intersect(array $a, array $b): array
    {
        return array_filter($a, fn($x) => $this->has($x, $b));
    }

    private function has($x, array $array): bool
    {
        return in_array($x, $array, true);
    }

    private function filterInputs(): array
    {
        return [
            ...$this->getFilterItems(),
            ...$this->getFilterItems(true),
        ];
    }

    public function setupFilter(string $reset_command): void
    {
        global $DIC;
        $this->initFilter();
        $this->setFilterCommand($this->getParentCmd());
        $this->setResetCommand($reset_command);
        array_map(fn($item) => $item->readFromSession(), $this->getFilterItems(true));
        $this->determineSelectedFilters();
        if ($DIC->ctrl()->getCmd() === $reset_command) {
            $this->resetFilter();
        } else {
            $this->writeFilterToSession();
        }
    }
}
