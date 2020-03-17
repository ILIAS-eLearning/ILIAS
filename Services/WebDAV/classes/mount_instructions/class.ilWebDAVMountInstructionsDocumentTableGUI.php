<?php

class ilWebDAVMountInstructionsDocumentTableGUI extends ilTable2GUI
{
    /** @var ilWebDAVUriBuilder */
    protected $mount_instructions_gui;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    /** @var ILIAS\UI\Renderer */
    protected $ui_renderer;

    /** @var bool */
    protected $is_editable = false;

    /** @var int */
    protected $factor = 10;

    /** @var int */
    protected $i = 1;

    /** @var int */
    protected $num_rendered_criteria = 0;

    /** @var array */
    protected $visible_optional_columns;

    /** @var ILIAS\UI\Component\Component[] */
    protected $ui_components = [];

    /** @var ilWebDAVMountInstructionsTableDataProvider */
    protected $provider;

    public function __construct(
        ilWebDAVMountInstructionsUploadGUI $a_parent_obj,
        ilWebDAVUriBuilder $a_webdav_uri_builder,
        string $a_command,
        ILIAS\UI\Factory $a_ui_factory,
        ILIAS\UI\Renderer $a_ui_renderer,
        bool $a_is_editable = false
    ) {
        $this->webdav_uri_builder = $a_webdav_uri_builder;
        $this->ui_factory = $a_ui_factory;
        $this->ui_renderer = $a_ui_renderer;
        $this->is_editable = $a_is_editable;

        $this->setId('mount_instructions_documents');
        $this->setFormName('mount_instructions_documents');

        parent::__construct($a_parent_obj, $a_command);

        $columns = $this->getColumnDefinition();
        $this->optional_columns = (array) $this->getSelectableColumns();
        $this->visible_optional_columns = (array) $this->getSelectedColumns();

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
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $a_command));

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('sorting');
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setLimit(PHP_INT_MAX);

        $this->setRowTemplate('tpl.webdav_documents_row.html', 'Services/WebDAV');

        if ($this->is_editable) {
            $this->setSelectAllCheckbox('webdav_id[]');
            $this->addCommandButton('saveDocumentSorting', $this->lng->txt('sorting_save'));
        }

        ilWebDAVMountInstructionsModalGUI::maybeRenderWebDAVModalInGlobalTpl();
    }

    public function setProvider(ilWebDAVMountInstructionsTableDataProvider $a_provider) : void
    {
        $this->provider = $a_provider;
    }

    public function getProvider() : ? ilWebDAVMountInstructionsTableDataProvider
    {
        return $this->provider;
    }

    protected function onBeforeDataFetched(array &$a_params, array &$a_filter) : void
    {
    }

    protected function prepareRow(array &$a_row) : void
    {
    }

    public function getSelectableColumns()
    {
        $optional_columns = array_filter($this->getColumnDefinition(), function ($column) {
            return isset($column['optional']) && $column['optional'];
        });

        $columns = [];
        foreach ($optional_columns as $index => $column) {
            $columns[$column['field']] = $column;
        }

        return $columns;
    }

    protected function isColumnVisible(int $a_index) : bool
    {
        $column_definition = $this->getColumnDefinition();
        if (array_key_exists($a_index, $column_definition)) {
            $column = $column_definition[$a_index];
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

    final protected function fillRow($a_row)
    {
        $this->prepareRow($a_row);

        foreach ($this->getColumnDefinition() as $index => $column) {
            if (!$this->isColumnVisible($index)) {
                continue;
            }

            $this->tpl->setCurrentBlock('column');
            $value = $this->formatCellValue($column['field'], $a_row);
            if ((string) $value === '') {
                $this->tpl->touchBlock('column');
            } else {
                $this->tpl->setVariable('COLUMN_VALUE', $value);
            }

            $this->tpl->parseCurrentBlock();
        }
    }

    protected function getColumnDefinition() : array
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

    public function populate() : void
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
        $filter = (array) $this->filter;

        foreach ($this->optional_filters as $key => $value) {
            if ($this->isFilterSelected($key)) {
                $filter[$key] = $value;
            }
        }

        $this->onBeforeDataFetched($params, $filter);
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

    protected function preProcessData(array &$data) : void
    {
        /**
         * @var  $key
         * @var  $document ilWebDAVMountInstructionsDocument
         */
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

    protected function formatCellValue(string $a_column, array $a_row) : string
    {
        if (in_array($a_column, ['creation_ts', 'modification_ts'])) {
            return \ilDatePresentation::formatDate(new \ilDateTime($a_row[$a_column], IL_CAL_DATETIME));
        } elseif ('sorting' === $a_column) {
            return $this->formatSorting($a_row);
        } elseif ('title' === $a_column) {
            return $this->formatTitle($a_column, $a_row);
        } elseif ('actions' === $a_column) {
            return $this->formatActionsDropDown($a_column, $a_row);
        } elseif ('language' === $a_column) {
            return $this->formatLanguage($a_column, $a_row);
        }

        return trim($a_row[$a_column]);
    }

    protected function formatActionsDropDown(string $a_column, array $a_row) : string
    {
        if (!$this->is_editable) {
            return '';
        }

        $this->ctrl->setParameter($this->getParentObject(), 'webdav_id', $a_row['id']);

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
                $this->lng->txt('webdav_sure_delete_documents_s') . ' ' . $a_row['title'],
                $this->ctrl->getFormAction($this->getParentObject(), 'deleteDocument')
            );

        $delete_btn = $this->ui_factory
            ->button()
            ->shy($this->lng->txt('delete'), '#')
            ->withOnClick($delete_modal->getShowSignal());

        $this->ui_components[] = $delete_modal;

        $this->ctrl->setParameter($this->getParentObject(), 'webdav_id', null);

        $drop_down = $this->ui_factory
            ->dropdown()
            ->standard([$edit_btn, $delete_btn])
            ->withLabel($this->lng->txt('actions'));

        return $this->ui_renderer->render($drop_down);
    }

    protected function formatLanguage(string $a_column, array $a_row) : string
    {
        return $a_row[$a_column];
    }

    protected function formatTitle(string $a_column, array $a_row)
    {
        if ($a_row['processed_text'] == null) {
            $a_row['processed_text'] = '';
        }

        global $DIC;
        $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
        $url = $uri_builder->getUriToMountInstructionModalByLanguage($a_row['language']);
        $title_link = $this->ui_factory
            ->button()
            ->shy($a_row[$a_column], '#')
            ->withAdditionalOnLoadCode(function ($id) use ($url) {
                return "$('#$id').click(function(){ triggerWebDAVModal('$url');});";
            });

        return $this->ui_renderer->render([$title_link]);
    }

    protected function formatSorting(array $a_row) : string
    {
        $value = ($this->i++) * $this->factor;
        if (!$this->is_editable) {
            return $value;
        }

        $sorting_field = new ilNumberInputGUI('', 'sorting[' . $a_row['id'] . ']');
        $sorting_field->setValue($value);
        $sorting_field->setMaxLength(4);
        $sorting_field->setSize(2);

        return $sorting_field->render();
    }

    public function getHTML()
    {
        return parent::getHTML() . $this->ui_renderer->render($this->ui_components);
    }
}
