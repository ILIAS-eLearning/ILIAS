<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilMailTemplateTableGUI
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailTemplateTableGUI extends ilTable2GUI
{
    /** @var \ilMailTemplateContext[] */
    protected $contexts = [];

    /** @var bool */
    protected $readOnly = false;

    /** @var Factory */
    protected $uiFactory;

    /** @var Renderer */
    protected $uiRenderer;

    /** @var ILIAS\UI\Component\Component[] */
    protected $uiComponents = [];

    /**
     * @param        $a_parent_obj
     * @param string $a_parent_cmd
     * @param Factory $uiFactory
     * @param Renderer $uiRenderer
     * @param bool $readOnly
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        Factory $uiFactory,
        Renderer $uiRenderer,
        $readOnly = false
    ) {
        $this->uiFactory = $uiFactory;
        $this->uiRenderer = $uiRenderer;
        $this->readOnly = $readOnly;

        $this->setId('mail_man_tpl');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt('mail_templates'));
        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('title');

        if (!$this->readOnly) {
            $this->addColumn('', '', '1%', true);
            $this->setSelectAllCheckbox('tpl_id');
            $this->addMultiCommand('confirmDeleteTemplate', $this->lng->txt('delete'));
        }

        $this->addColumn($this->lng->txt('title'), 'title', '30%');
        $this->addColumn($this->lng->txt('mail_template_context'), 'context', '20%');
        $this->addColumn($this->lng->txt('action'), '', '10%');

        $this->setRowTemplate('tpl.mail_template_row.html', 'Services/Mail');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->contexts = \ilMailTemplateContextService::getTemplateContexts();
    }

    /**
     * @param  string $column
     * @param array   $row
     * @return string
     */
    protected function formatCellValue($column, array $row)
    {
        if ('tpl_id' === $column) {
            return \ilUtil::formCheckbox(false, 'tpl_id[]', $row[$column]);
        } else {
            if ('lang' === $column) {
                return $this->lng->txt('meta_l_' . $row[$column]);
            } else {
                if ($column == 'context') {
                    if (isset($this->contexts[$row[$column]])) {
                        $isDefaultSuffix = '';
                        if ($row['is_default']) {
                            $isDefaultSuffix = $this->lng->txt('mail_template_default');
                        }

                        return implode('', [
                            $this->contexts[$row[$column]]->getTitle(),
                            $isDefaultSuffix
                        ]);
                    } else {
                        return $this->lng->txt('mail_template_orphaned_context');
                    }
                }
            }
        }

        return $row[$column];
    }

    /**
     * @inheritdoc
     */
    protected function fillRow($row)
    {
        foreach ($row as $column => $value) {
            if ($column === 'tpl_id' && $this->readOnly) {
                continue;
            }

            $value = $this->formatCellValue($column, $row);
            $this->tpl->setVariable('VAL_' . strtoupper($column), $value);
        }

        $this->tpl->setVariable('VAL_ACTION', $this->formatActionsDropDown($row));
    }

    /**
     * @param array $row
     * @return string
     */
    protected function formatActionsDropDown(array $row) : string
    {
        $this->ctrl->setParameter($this->getParentObject(), 'tpl_id', $row['tpl_id']);

        $buttons = [];

        if (count($this->contexts) > 0) {
            if (!$this->readOnly) {
                $buttons[] = $this->uiFactory
                    ->button()
                    ->shy(
                        $this->lng->txt('edit'),
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'showEditTemplateForm')
                    );
            } else {
                $buttons[] = $this->uiFactory
                    ->button()
                    ->shy(
                        $this->lng->txt('view'),
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'showEditTemplateForm')
                    );
            }
        }

        if (!$this->readOnly) {
            $deleteModal = $this->uiFactory
                ->modal()
                ->interruptive(
                    $this->lng->txt('delete'),
                    $this->lng->txt('mail_tpl_sure_delete_entry'),
                    $this->ctrl->getFormAction($this->getParentObject(), 'deleteTemplate')
                );

            $this->uiComponents[] = $deleteModal;

            $buttons[] = $this->uiFactory
                ->button()
                ->shy(
                    $this->lng->txt('delete'),
                    $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteTemplate')
                )->withOnClick($deleteModal->getShowSignal());

            if ($row['is_default']) {
                $buttons[] = $this->uiFactory
                    ->button()
                    ->shy(
                        $this->lng->txt('mail_template_unset_as_default'),
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'unsetAsContextDefault')
                    );
            } else {
                $buttons[] = $this->uiFactory
                    ->button()
                    ->shy(
                        $this->lng->txt('mail_template_set_as_default'),
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'setAsContextDefault')
                    );
            }
        }

        $this->ctrl->setParameter($this->getParentObject(), 'tpl_id', null);

        $dropDown = $this->uiFactory
            ->dropdown()
            ->standard($buttons)
            ->withLabel($this->lng->txt('actions'));

        return $this->uiRenderer->render([$dropDown]);
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        return parent::getHTML() . $this->uiRenderer->render($this->uiComponents);
    }
}
