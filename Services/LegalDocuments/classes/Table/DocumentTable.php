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

use ILIAS\LegalDocuments\Value\CriterionContent;
use Closure;
use ILIAS\LegalDocuments\TableConfig;
use ILIAS\LegalDocuments\Table;
use ILIAS\LegalDocuments\Table\DocumentModal;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\TableSelection;
use ilLanguage;
use ilDatePresentation;
use ilDateTime;
use ilNumberInputGUI;
use DateTimeImmutable;

class DocumentTable implements Table
{
    /** @var Closure(class-string): object<class-string> */
    private readonly Closure $create;
    /** @var Closure(DateTimeImmutable): string */
    private readonly Closure $format_date;

    /**
     * @param Closure(CriterionContent): Component $criterion_as_component
     * @param null|Closure(class-string): object<class-string> $create
     * @param Closure(DateTimeImmutable): string $format_date
     */
    public function __construct(
        private readonly Closure $criterion_as_component,
        private readonly DocumentRepository $repository,
        private readonly UI $ui,
        private readonly DocumentModal $modal,
        ?Closure $create = null,
        ?Closure $format_date = null
    ) {
        $this->create = $create ?? fn($class, ...$args) => new $class(...$args);
        $this->format_date = $format_date ?? fn(DateTimeImmutable $date) => ilDatePresentation::formatDate(new ilDateTime($date->getTimestamp(), IL_CAL_UNIX));
    }

    public function columns(): array
    {
        return [
            'order' => [$this->ui->txt('tbl_docs_head_sorting'), '', '5%'],
            'title' => [$this->ui->txt('tbl_docs_head_title'), '', '25%'],
            'created' => [$this->ui->txt('tbl_docs_head_created')],
            'change' => [$this->ui->txt('tbl_docs_head_last_change')],
            'criteria' => [$this->ui->txt('tbl_docs_head_criteria')],
        ];
    }

    public function config(TableConfig $config): void
    {
        $config->setTitle($this->ui->txt('tbl_docs_title'));
        $config->setSelectableColumns('created', 'change');
    }

    public function rows(TableSelection $select): array
    {
        return array_map($this->row(...), $this->select($select));
    }

    public function select(TableSelection $select): array
    {
        $select->setMaxCount($this->repository->countAll());
        return $this->repository->all($select->getOffset(), $select->getLimit());
    }

    public function row(Document $document): array
    {
        $render_order = $this->orderInputGui($document);

        return [
            'order' => fn() => $render_order->render(),
            'title' => $this->modal->create($document->content()),
            'created' => ($this->format_date)($document->meta()->creation()->time()),
            'change' => ($this->format_date)($document->meta()->lastModification()->time()),
            'criteria' => $this->showCriteria($document, $this->showCriterion(...)),
        ];
    }

    /**
     * @param Closure(Criterion): list<Component> $proc
     */
    public function showCriteria(Document $document, Closure $proc)
    {
        if ([] === $document->criteria()) {
            return $this->ui->txt('tbl_docs_cell_not_criterion');
        }
        return array_merge(...array_map(
            $proc,
            $document->criteria()
        ));
    }

    /**
     * @return list<Component>
     */
    public function showCriterion(Criterion $criterion): array
    {
        return [
            $this->criterionName($criterion),
            $this->ui->create()->legacy('<br/>'),
        ];
    }

    public function criterionName(Criterion $criterion): Component
    {
        return ($this->criterion_as_component)($criterion->content());
    }

    public function orderInputGui(Document $document): ilNumberInputGUI
    {
        $input = ($this->create)(ilNumberInputGUI::class, '', 'order[' . $document->id() . ']');
        $input->setValue((string) $document->meta()->sorting());
        $input->setMaxLength(4);
        $input->setSize(2);
        $input->setDisabled(true);

        return $input;
    }

    public function ui(): UI
    {
        return $this->ui;
    }

    public function name(): string
    {
        return self::class;
    }
}
