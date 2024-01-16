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
        return $this->table->mapSelection($this->row(...), $select);
    }

    public function row(Document $document, int $sorting): array
    {
        $link = $this->table->ui()->create()->link()->standard(...);

        return [
            'delete' => fn() => ilLegacyFormElementsUtil::formCheckbox(false, 'ids[]', (string) $document->id()),
            'order' => $this->orderInputGui($document, $sorting),
            ...array_slice($this->table->row($document, $sorting), 1),
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
        return function (Criterion $criterion) use ($document) {
            $modal = $this->table->ui()->create()->modal()->interruptive(
                $this->table->ui()->txt('doc_detach_crit_confirm_title'),
                $this->table->ui()->txt('doc_sure_detach_crit'),
                $this->edit_links->deleteCriterion($document, $criterion)
            );

            return [
                $this->table->ui()->create()->legacy('<div style="display: flex">'),
                $this->table->criterionName($criterion),
                $this->table->ui()->create()->legacy('&nbsp;'),
                $modal,
                $this->table->ui()->create()->dropdown()->standard([
                    $this->table->ui()->create()->link()->standard($this->table->ui()->txt('edit'), $this->edit_links->editCriterion($document, $criterion)),
                    $this->table->ui()->create()->button()->shy($this->table->ui()->txt('delete'), '')->withOnClick($modal->getShowSignal()),
                ]),
                $this->table->ui()->create()->legacy('</div>'),
            ];
        };
    }

    private function orderInputGui($document, $step): Closure
    {
        $input = $this->table->orderInputGui($document, $step);
        $input->setDisabled(false);

        return $input->render(...);
    }
}
