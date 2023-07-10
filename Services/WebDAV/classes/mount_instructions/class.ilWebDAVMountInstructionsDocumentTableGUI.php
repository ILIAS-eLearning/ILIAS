<?php

declare(strict_types=1);

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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\RequestInterface;

class ilWebDAVMountInstructionsDocumentTableGUI extends ilTable2GUI
{
    protected ilWebDAVUriBuilder $webdav_uri_builder;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected RequestInterface $request;
    protected bool $is_editable = false;
    protected int $factor = 10;
    protected int $i = 1;
    protected int $num_rendered_criteria = 0;
    protected array $optional_columns;
    protected array $visible_optional_columns;

    /** @var ILIAS\UI\Component\Component[] */
    protected array $ui_components = [];

    protected ?ilWebDAVMountInstructionsTableDataProvider $provider = null;

    public function __construct(
        ilWebDAVMountInstructionsUploadGUI $parent_obj,
        ilWebDAVUriBuilder $webdav_uri_builder,
        string $command,
        Factory $ui_factory,
        Renderer $ui_renderer,
        RequestInterface $request,
        bool $is_editable = false
    ) {
        $this->webdav_uri_builder = $webdav_uri_builder;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->is_editable = $is_editable;
        $this->request = $request;

        $this->setId('mount_instructions_documents');
        $this->setFormName('mount_instructions_documents');

        parent::__construct($parent_obj, $command);

        $columns = $this->getColumnDefinition();
        $this->optional_columns = $this->getSelectableColumns();
        $this->visible_optional_columns = $this->getSelectedColumns();

        foreach ($columns as $index => $column) {
            if ($this->isColumnVisible($index)) {
                $this->addColumn(
                    $column['txt'],
                    isset($column['sortable']) && $column['sortable'] ? $column['field'] : '',
                    isset($column['width']) ? $column['width'] : '',
                    isset($column['is_checkbox']) ? (bool) $column['is_checkbox'] : false
                );
            }
        }

        $this->setTitle($this->lng->txt('webdav_tbl_docs_title'));
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $command));

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('sorting');
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setLimit(PHP_INT_MAX);

        $this->setRowTemplate('tpl.webdav_documents_row.html', 'Services/WebDAV');

        if ($this->is_editable) {
            $this->addCommandButton('saveDocumentSorting', $this->lng->txt('sorting_save'));
        }

        ilWebDAVMountInstructionsModalGUI::maybeRenderWebDAVModalInGlobalTpl();
    }

    public function setProvider(ilWebDAVMountInstructionsTableDataProvider $provider): void
    {
        $this->provider = $provider;
    }

    public function getProvider(): ?ilWebDAVMountInstructionsTableDataProvider
    {
        return $this->provider;
    }

    public function getSelectableColumns(): array
    {
        $optional_columns = array_filter($this->getColumnDefinition(), fn ($column) => isset($column['optional']) && $column['optional']);

        $columns = [];
        foreach ($optional_columns as $column) {
            $columns[$column['field']] = $column;
        }

        return $columns;
    }

    protected function isColumnVisible(int $index): bool
    {
        $column_definition = $this->getColumnDefinition();
        if (array_key_exists($index, $column_definition)) {
            $column = $column_definition[$index];
            if (isset($column['optional']) && !$column['optional']) {
                return true;
            }

            if (
                is_array($this->visible_optional_columns) &&
                array_key_exists($column['field'], $this->visible_optional_columns)
            ) {
                return true;
            }
        }

        return false;
    }

    final protected function fillRow(array $row): void
    {
        foreach ($this->getColumnDefinition() as $index => $column) {
            if (!$this->isColumnVisible($index)) {
                continue;
            }

            $this->tpl->setCurrentBlock('column');
            $value = $this->formatCellValue($column['field'], $row);
            if ($value === '') {
                $this->tpl->touchBlock('column');
            } else {
                $this->tpl->setVariable('COLUMN_VALUE', $value);
            }

            $this->tpl->parseCurrentBlock();
        }
    }

    protected function getColumnDefinition(): array
    {
        $i = 0;

        $columns = [];

        $columns[++$i] = [
            'field' => 'sorting',
            'txt' => $this->lng->txt('meta_order', 'meta'),
            'default' => true,
            'optional' => false,
            'sortable' => false,
            'width' => '5%'
        ];

        $columns[++$i] = [
            'field' => 'title',
            'txt' => $this->lng->txt('webdav_tbl_docs_head_title'),
            'default' => true,
            'optional' => false,
            'sortable' => false,
            'width' => '25%'
        ];

        $columns[++$i] = [
            'field' => 'creation_ts',
            'txt' => $this->lng->txt('created'),
            'default' => true,
            'optional' => true,
            'sortable' => false
        ];

        $columns[++$i] = [
            'field' => 'modification_ts',
            'txt' => $this->lng->txt('last_change'),
            'default' => true,
            'optional' => true,
            'sortable' => false
        ];

        $columns[++$i] = [
            'field' => 'language',
            'txt' => $this->lng->txt('language'),
            'default' => true,
            'optional' => false,
            'sortable' => false
        ];

        if ($this->is_editable) {
            $columns[++$i] = [
                'field' => 'actions',
                'txt' => $this->lng->txt('actions'),
                'default' => true,
                'optional' => false,
                'sortable' => false,
                'width' => '10%'
            ];
        };

        return $columns;
    }

    public function populate(): void
    {
        if ($this->getExternalSegmentation() && $this->getExternalSorting()) {
            $this->determineOffsetAndOrder();
        } elseif (!$this->getExternalSegmentation() && $this->getExternalSorting()) {
            $this->determineOffsetAndOrder(true);
        }

        $params = [];
        if ($this->getExternalSegmentation()) {
            $params['limit'] = $this->getLimit();
            $params['offset'] = $this->getOffset();
        }
        if ($this->getExternalSorting()) {
            $params['order_field'] = $this->getOrderField();
            $params['order_direction'] = $this->getOrderDirection();
        }

        $this->determineSelectedFilters();
        $data = $this->getProvider()->getList();

        if (!count($data['items']) && $this->getOffset() > 0 && $this->getExternalSegmentation()) {
            $this->resetOffset();
            if ($this->getExternalSegmentation()) {
                $params['limit'] = $this->getLimit();
                $params['offset'] = $this->getOffset();
            }
            $data = $this->getProvider()->getList();
        }

        $this->preProcessData($data);

        $this->setData($data['items']);
        if ($this->getExternalSegmentation()) {
            $this->setMaxCount($data['cnt']);
        }
    }

    protected function preProcessData(array &$data): void
    {
        foreach ($data['items'] as $key => $document) {
            $data['items'][$key] = [
                'id' => $document->getId(),
                'title' => $document->getTitle(),
                'creation_ts' => $document->getCreationTs(),
                'modification_ts' => $document->getModificationTs(),
                'raw_text' => $document->getUploadedInstructions(),
                'processed_text' => $document->getProcessedInstructions(),
                'language' => $document->getLanguage(),
            ];
        }
    }

    protected function formatCellValue(string $column, array $row): string
    {
        $function = 'format' . ucfirst($column);
        if (method_exists($this, $function)) {
            return $this->{$function}($column, $row);
        }
        if (in_array($column, ['creation_ts', 'modification_ts'])) {
            return ilDatePresentation::formatDate(new ilDateTime($row[$column], IL_CAL_DATETIME));
        }

        return trim($row[$column]);
    }

    protected function formatActions(string $column, array $row): string
    {
        if (!$this->is_editable) {
            return '';
        }

        $this->ctrl->setParameter($this->getParentObject(), 'document_id', $row['id']);

        $edit_btn = $this->ui_factory
            ->button()
            ->shy(
                $this->lng->txt('edit'),
                $this->ctrl->getLinkTarget($this->getParentObject(), 'showEditDocumentForm')
            );

        $delete_modal = $this->ui_factory
            ->modal()
            ->interruptive(
                $this->lng->txt('webdav_doc_delete'),
                $this->lng->txt('webdav_sure_delete_documents_s') . ' ' . $row['title'],
                $this->ctrl->getFormAction($this->getParentObject(), 'deleteDocument')
            );

        $delete_btn = $this->ui_factory
            ->button()
            ->shy($this->lng->txt('delete'), '#')
            ->withOnClick($delete_modal->getShowSignal());

        $this->ui_components[] = $delete_modal;

        $this->ctrl->setParameter($this->getParentObject(), 'document_id', null);

        $drop_down = $this->ui_factory
            ->dropdown()
            ->standard([$edit_btn, $delete_btn])
            ->withLabel($this->lng->txt('actions'));

        return $this->ui_renderer->render($drop_down);
    }

    protected function formatTitle(string $column, array $row): string
    {
        if ($row['processed_text'] == null) {
            $row['processed_text'] = '';
        }

        $uri_builder = new ilWebDAVUriBuilder($this->request);
        $url = $uri_builder->getUriToMountInstructionModalByLanguage($row['language']);
        $title_link = $this->ui_factory
            ->button()
            ->shy($row[$column], '#')
            ->withAdditionalOnLoadCode(fn ($id) => "$('#$id').click(function(){ triggerWebDAVModal('$url');});");

        return $this->ui_renderer->render([$title_link]);
    }

    protected function formatSorting(string $column, array $row): string
    {
        $value = strval(($this->i++) * $this->factor);
        if (!$this->is_editable) {
            return $value;
        }

        $sorting_field = new ilNumberInputGUI('', 'sorting[' . $row['id'] . ']');
        $sorting_field->setValue($value);
        $sorting_field->setMaxLength(4);
        $sorting_field->setSize(2);

        return $sorting_field->render();
    }

    public function getHTML(): string
    {
        return parent::getHTML() . $this->ui_renderer->render($this->ui_components);
    }
}
