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
use DateTimeImmutable;
use Generator;
use ilCtrl;
use ilCtrlInterface;
use ilDatePresentation;
use ilDateTime;
use ILIAS\Data\Factory;
use ILIAS\Data\URI;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\EditLinks;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\Action\Single;
use ILIAS\UI\Component\Table\OrderingBinding;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\UI\Implementation\Component\Table\Action\Multi;
use ILIAS\UI\Implementation\Component\Table\Ordering;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class DocumentTable implements OrderingBinding
{
    public const CMD_EDIT_DOCUMENT = 'editDocument';
    public const CMD_DELETE_DOCUMENT = 'deleteDocument';
    public const CMD_DELETE_DOCUMENTS = 'deleteDocuments';
    public const CMD_ADD_CRITERION = 'addCriterion';

    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Factory $data_factory;
    private readonly ilCtrl|ilCtrlInterface $ctrl;
    private readonly Ordering $table;
    private readonly Renderer $ui_renderer;
    /**
     * @var Document[]
     */
    private array $records;

    public function __construct(
        private readonly Closure                     $criterion_as_component,
        private readonly DocumentRepository          $repository,
        private readonly UI                          $ui,
        private readonly DocumentModal               $modal,
        private readonly object                      $gui,
        private readonly ?EditLinks                  $edit_links = null,
        ServerRequestInterface|RequestInterface|null $request = null,
        ?Factory                                     $data_factory = null,
        ?ilCtrl                                      $ctrl = null,
        ?Renderer                                    $ui_renderer = null,
    )
    {
        global $DIC;
        $this->request = $request ?: $DIC->http()->request();
        $this->data_factory = $data_factory ?: new Factory();
        $this->ctrl = $ctrl ?: $DIC->ctrl();
        $this->ui_renderer = $ui_renderer ?: $DIC->ui()->renderer();

        $this->table = $this->buildTable();
        $this->records = $this->repository->all();
    }

    private function buildTable(): Ordering
    {
        $uiTable = $this->ui->create()->table();
        $table = $uiTable->ordering(
            $this->ui->txt('tbl_docs_title'),
            [
                'title' => $uiTable->column()->text($this->ui->txt('tbl_docs_head_title')),
                'created' => $uiTable->column()->text($this->ui->txt('tbl_docs_head_created')),
                'change' => $uiTable->column()->text($this->ui->txt('tbl_docs_head_last_change')),
                'criteria' => $uiTable->column()->text($this->ui->txt('tbl_docs_head_criteria')),
            ],
            $this,
            (new URI((string) $this->request->getUri()))->withParameter("cmd", "saveOrder")
        )
            ->withId('legalDocsTable')
            ->withRequest($this->request);

        if ($this->edit_links) {
            $table = $table->withActions($this->buildTableActions());
        }
        return $table;
    }

    public function getRows(
        OrderingRowBuilder $row_builder,
        array              $visible_column_ids
    ): Generator
    {
        foreach ($this->buildTableRows($this->records) as $row) {
            $row['created'] = $this->formatDateRow($row['created']);
            $row['change'] = $this->formatDateRow($row['change']);

            yield $row_builder->buildOrderingRow((string) $row['id'], $row);
        }
    }

    /**
     * @param Document[] $documents
     * @return array
     */
    private function buildTableRows(array $documents): array
    {
        $table_rows = [];

        foreach ($documents as $document) {
            $criterion_components = [];
            foreach ($document->criteria() as $criterion) {
                $criterion_components[] = $this->ui->create()->legacy('<div style="display: flex; gap: 1rem;">');
                $criterion_components[] = $this->criterionName($criterion);

                if ($this->edit_links) {
                    $delete_modal = $this->ui->create()->modal()->interruptive(
                        $this->ui->txt('doc_detach_crit_confirm_title'),
                        $this->ui->txt('doc_sure_detach_crit'),
                        $this->edit_links->deleteCriterion($document, $criterion)
                    );

                    $dropdown = $this->ui->create()->dropdown()->standard([
                        $this->ui->create()->button()->shy(
                            $this->ui->txt('edit'),
                            $this->edit_links->editCriterion($document, $criterion)
                        ),
                        $this->ui->create()->button()->shy(
                            $this->ui->txt('delete'),
                            ''
                        )->withOnClick($delete_modal->getShowSignal())
                    ]);

                    $criterion_components[] = $delete_modal;
                    $criterion_components[] = $dropdown;
                }

                $criterion_components[] = $this->ui->create()->legacy('</div>');
            }

            $table_rows[] = [
                'id' => $document->id(),
                'title' => $this->ui_renderer->render($this->modal->create($document->content())),
                'created' => $document->meta()->creation()->time(),
                'change' => $document->meta()->lastModification()->time(),
                'criteria' => $this->ui_renderer->render($criterion_components),
            ];
        }
        return $table_rows;
    }

    public function criterionName(Criterion $criterion): Component
    {
        return ($this->criterion_as_component)($criterion->content());
    }

    private function formatDateRow(DateTimeImmutable $date): string
    {
        return ilDatePresentation::formatDate(new ilDateTime($date->getTimestamp(), IL_CAL_UNIX));
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->repository->countAll();
    }

    public function render(): string
    {
        return $this->ui_renderer->render($this->table);
    }

    private function buildTableActions(): array
    {
        return [
            self::CMD_DELETE_DOCUMENTS => $this->buildTableAction(self::CMD_DELETE_DOCUMENTS, $this->ui->txt('delete'), true),
            self::CMD_EDIT_DOCUMENT => $this->buildTableAction(self::CMD_EDIT_DOCUMENT, $this->ui->txt('edit')),
            self::CMD_ADD_CRITERION => $this->buildTableAction(
                self::CMD_ADD_CRITERION,
                $this->ui->txt('tbl_docs_action_add_criterion')
            ),
            self::CMD_DELETE_DOCUMENT => $this->buildTableAction(self::CMD_DELETE_DOCUMENT, $this->ui->txt('delete')),
        ];
    }

    private function buildTableAction(string $cmd, string $title, bool $multi = false): Single|Multi
    {
        $uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this->gui, $cmd)
        );

        /**
         * @var URLBuilder $url_builder
         * @var URLBuilderToken $action_parameter_token ,
         * @var URLBuilderToken $row_id_token
         */
        [
            $url_builder,
            $action_parameter_token,
            $row_id_token
        ] = (new URLBuilder($uri))->acquireParameters(
            ['legal_document'],
            'action',
            'id'
        );

        if ($multi) {
            return $this->ui->create()->table()->action()->multi(
                $title,
                $url_builder->withParameter($action_parameter_token, $cmd),
                $row_id_token
            );
        }
        return $this->ui->create()->table()->action()->single(
            $title,
            $url_builder->withParameter($action_parameter_token, $cmd),
            $row_id_token
        );
    }
}
