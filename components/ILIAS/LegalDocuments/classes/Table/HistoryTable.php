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

namespace ILIAS\LegalDocuments\Table;

use Closure;
use ILIAS\LegalDocuments\TableConfig;
use ILIAS\LegalDocuments\Table;
use ILIAS\LegalDocuments\Value\History;
use ILIAS\LegalDocuments\Repository\HistoryRepository;
use ILIAS\LegalDocuments\TableSelection;
use ILIAS\LegalDocuments\Table\DocumentModal;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Legacy\ResettingDurationInputGUI;
use ilLanguage;
use ilTextInputGUI;
use ilDateDurationInputGUI;
use ilDateTime;
use ilDatePresentation;
use ilObjUser;
use DateTimeImmutable;

/**
 * @implements Table<History>
 */
class HistoryTable implements Table
{
    /** @var Closure(DateTimeImmutable): string */
    private readonly Closure $format_date;

    /**
     * @param Closure(class-string): object<class-string> $create
     * @param null|Closure(DateTimeImmutable): string $create
     */
    public function __construct(
        private readonly HistoryRepository $repository,
        private readonly ProvideDocument $document,
        public readonly string $reset_command,
        private readonly string $auto_complete_link,
        private readonly UI $ui,
        private readonly DocumentModal $modal,
        private readonly Closure $create,
        ?Closure $format_date = null
    ) {
        $this->format_date = $format_date ?? fn(DateTimeImmutable $date) => ilDatePresentation::formatDate(new ilDateTime($date->getTimestamp(), IL_CAL_UNIX));
    }

    public function columns(): array
    {
        return [
            'created' => [$this->ui->txt('tbl_hist_head_acceptance_date'), 'created'],
            'login' => [$this->ui->txt('tbl_hist_head_login'), 'login'],
            'firstname' => [$this->ui->txt('tbl_hist_head_firstname'), 'firstname'],
            'lastname' => [$this->ui->txt('tbl_hist_head_lastname'), 'lastname'],
            'document' => [$this->ui->txt('tbl_hist_head_document'), 'document'],
            'criteria' => [$this->ui->txt('tbl_hist_head_criteria')],
        ];
    }

    public function config(TableConfig $config): void
    {
        $config->setTitle($this->ui->txt('acceptance_history'));
        $config->setDefaultOrderField('created');
        $config->setDefaultOrderDirection('DESC');
        $config->setSelectableColumns('firstname', 'lastname', 'criteria');

        $filter = $config->asFilter($this->reset_command);
        $filter->addFilterItem($this->userName());
        $filter->addFilterItem($this->time(), true);
    }

    public function rows(TableSelection $selection): array
    {
        $filter = $selection->filter();
        $period = $filter['period'] ?? [];
        unset($filter['period']);
        $filter = array_filter([...$filter, ...$period]);

        $selection->setMaxCount($this->repository->countAll($filter));

        return array_map($this->row(...), $this->repository->all(
            $filter,
            [$selection->getOrderField() => $selection->getOrderDirection()],
            $selection->getOffset(),
            $selection->getLimit()
        ));
    }

    public function name(): string
    {
        return self::class;
    }

    private function row(History $record): array
    {
        $user = ($this->create)(ilObjUser::class, $record->creation()->user());

        return [
            'created' => ($this->format_date)($record->creation()->time()),
            'login' => $user?->getLogin() ?? $this->ui->txt('deleted'),
            'firstname' => $user?->getFirstname() ?? '-',
            'lastname' => $user?->getLastname() ?? '-',
            'document' => $this->modal->create($record->documentContent()),
            'criteria' => $this->showCriteria($record),
        ];
    }

    private function userName(): ilTextInputGUI
    {
        $ul = ($this->create)(ilTextInputGUI::class, join('/', array_map($this->ui->txt(...), ['login', 'email', 'name'])), 'query');
        $ul->setDataSource($this->auto_complete_link);
        $ul->setSize(20);
        $ul->setSubmitFormOnEnter(true);
        return $ul;
    }

    private function time(): ilDateDurationInputGUI
    {
        class_exists(ilDateTime::class); // Trigger autoloader to ensure IL_CAL_UNIX is defined.
        $duration = ($this->create)(ResettingDurationInputGUI::class, $this->ui->txt('period'), 'period');
        $duration->setAllowOpenIntervals(true);
        $duration->setShowTime(true);
        $duration->setStartText($this->ui->txt('period_from'));
        $duration->setEndText($this->ui->txt('period_until'));
        $duration->setStart(($this->create)(ilDateTime::class, null, IL_CAL_UNIX));
        $duration->setEnd(($this->create)(ilDateTime::class, null, IL_CAL_UNIX));

        return $duration;
    }

    /**
     * @return string|list<Component>
     */
    private function showCriteria(History $record)
    {
        $conv = $this->document->toCondition(...);
        $components = array_map(fn($c) => $conv($c)->asComponent(), $record->criteriaContent());

        return $components === [] ?
                           $this->ui->txt('tbl_hist_cell_not_criterion') :
                           $components;
    }
}
