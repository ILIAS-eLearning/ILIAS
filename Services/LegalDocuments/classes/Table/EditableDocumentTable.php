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
use ILIAS\LegalDocuments\TableSelection;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\EditLinks;
use ilLegacyFormElementsUtil;

class EditableDocumentTable implements Table
{
    public function __construct(
        private readonly DocumentTable $table,
        private readonly EditLinks $edit_links
    ) {
    }

    public function columns(): array
    {
        return [
            'delete' => [' ', '', '1%', true],
            ...$this->table->columns(),
            'actions' => [$this->table->ui()->txt('actions'), '', '10%'],
        ];
    }

    public function config(TableConfig $config): void
    {
        $config->addMultiCommand('deleteDocuments', $this->table->ui()->txt('delete'));
        $config->addCommandButton('saveOrder', $this->table->ui()->txt('sorting_save'));
        $this->table->config($config);
    }

    public function rows(TableSelection $select): array
    {
        return array_map($this->row(...), $this->table->select($select));
    }

    public function row(Document $document): array
    {
        $link = $this->table->ui()->create()->link()->standard(...);

        return [
            'delete' => fn() => ilLegacyFormElementsUtil::formCheckbox(false, 'ids[]', (string) $document->id()),
            'order' => $this->orderInputGui($document),
            ...array_slice($this->table->row($document), 1),
            'criteria' => $this->table->showCriteria($document, $this->willShowCriterion($document)),
            'actions' => $this->table->ui()->create()->dropdown()->standard([
                $link($this->table->ui()->txt('edit'), $this->edit_links->editDocument($document)),
                $link($this->table->ui()->txt('tbl_docs_action_add_criterion'), $this->edit_links->addCriterion($document)),
                $link($this->table->ui()->txt('delete'), $this->edit_links->deleteDocument($document)),
            ]),
        ];
    }

    public function name(): string
    {
        return self::class;
    }

    private function willShowCriterion($document): Closure
    {
        $link = $this->table->ui()->create()->link()->standard(...);

        return fn(Criterion $criterion) => [
            $this->table->ui()->create()->legacy('<div style="display: flex">'),
            $this->table->criterionName($criterion),
            $this->table->ui()->create()->legacy('&nbsp;'),
            $this->table->ui()->create()->dropdown()->standard([
                $link($this->table->ui()->txt('edit'), $this->edit_links->editCriterion($document, $criterion)),
                $link($this->table->ui()->txt('delete'), $this->edit_links->deleteCriterion($document, $criterion)),
            ]),
            $this->table->ui()->create()->legacy('</div>'),
        ];
    }

    private function orderInputGui($document): Closure
    {
        $input = $this->table->orderInputGui($document);
        $input->setDisabled(false);

        return fn() => $input->render();
    }
}
