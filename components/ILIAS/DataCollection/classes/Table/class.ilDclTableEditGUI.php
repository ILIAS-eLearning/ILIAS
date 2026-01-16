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

use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\Checkbox;

class ilDclTableEditGUI
{
    private ?int $table_id;
    private ilDclTable $table;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected Form $form;
    protected ilHelpGUI $help;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilDclTableListGUI $parent_object;
    protected int $obj_id;

    public function __construct(ilDclTableListGUI $a_parent_obj)
    {
        global $DIC;

        $locator = $DIC['ilLocator'];

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->parent_object = $a_parent_obj;
        $this->obj_id = $a_parent_obj->getObjId();
        $this->help = $DIC->help();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $table_id = null;
        if ($this->http->wrapper()->query()->has('table_id')) {
            $table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        }

        $this->table_id = $table_id;
        $this->table = ilDclCache::getTableCache($this->table_id);

        $this->ctrl->saveParameter($this, 'table_id');
        if ($this->table->getTitle()) {
            $locator->addItem($this->table->getTitle(), $this->ctrl->getLinkTarget($this, 'edit'));
        }
        $this->tpl->setLocator();

        if (!$this->checkAccess()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilDclRecordListGUI::class, 'listRecords');
        }
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        if ($cmd === 'update') {
            $this->save(false);
        } else {
            $this->$cmd();
        }
    }

    public function create(): void
    {
        $this->help->setSubScreenId('create');
        $this->tpl->setContent($this->lng->txt('dcl_new_table') . $this->ui_renderer->render($this->initForm()));
    }

    public function edit(): void
    {
        $this->help->setSubScreenId('edit');
        $this->tpl->setContent(
            sprintf($this->lng->txt('dcl_edit_table'), $this->table->getTitle()) .
            $this->ui_renderer->render($this->initForm(false))
        );
    }

    public function initForm(bool $create = true): Form
    {
        $f = $this->ui_factory->input()->field();
        $inputs = [];

        $edit = [];

        $edit['title'] = $f->text($this->lng->txt('title'))->withRequired(true);
        $edit['description'] = $f->markdown(new ilUIMarkdownPreviewGUI(), $this->lng->txt('additional_info'));
        $edit['visible'] = $this->checkbox('visible');
        $inputs['edit'] = $f->section($edit, $this->lng->txt('general_settings'));

        $table = [];
        if (!$create) {
            $options = [];
            foreach ($this->table->getFields() as $field) {
                if ($field->getId() !== 'comments' && $field->getRecordQuerySortObject() !== null) {
                    $options[$field->getId()] = $field->getTitle();
                }
            }
            $table['default_sort_field'] = $f->select(
                $this->lng->txt('dcl_default_sort_field'),
                $options
            );

            $table['default_sort_field_order'] = $f->select(
                $this->lng->txt('dcl_default_sort_field_order'),
                ['asc' => $this->lng->txt('dcl_asc'), 'desc' => $this->lng->txt('dcl_desc')],
                $this->lng->txt('dcl_default_sort_field_order_desc')
            );
        }
        $table['export_enabled'] = $this->checkbox('export_enabled');
        $table['import_enabled'] = $this->checkbox('import_enabled');
        $table['comments_enabled'] = $this->checkbox('comments');
        $inputs['table'] = $f->section($table, $this->lng->txt('dcl_table_settings'));

        $record = [];
        $record['add_perm'] = $f->optionalGroup(
            ['save_confirmation' => $this->checkbox('save_confirmation')],
            $this->lng->txt('dcl_add_perm'),
            $this->lng->txt('dcl_add_perm_desc')
        )->withValue(['save_confirmation' => false]);
        $record['edit_perm'] = $f->radio($this->lng->txt('dcl_edit_perm'))
            ->withOption('all', $this->lng->txt('dcl_all_entries'))
            ->withOption('own', $this->lng->txt('dcl_own_entries'))
            ->withOption('none', $this->lng->txt('dcl_no_entries'))
            ->withValue('own');
        $record['delete_perm'] = $f->radio($this->lng->txt('dcl_delete_perm'))
            ->withOption('all', $this->lng->txt('dcl_all_entries'))
            ->withOption('own', $this->lng->txt('dcl_own_entries'))
            ->withOption('none', $this->lng->txt('dcl_no_entries'))
            ->withValue('own');
        $record['view_own_records_perm'] = $this->checkbox('view_own_records_perm');
        $record['limited'] = $f->optionalGroup(
            [
                'limit_start' => $f->dateTime($this->lng->txt('dcl_limit_start'))->withUseTime(true),
                'limit_end' => $f->dateTime($this->lng->txt('dcl_limit_end'))->withUseTime(true)
            ],
            $this->lng->txt('dcl_limited'),
            $this->lng->txt('dcl_limited_desc')
        )->withValue(null);
        $inputs['record'] = $f->section($record, $this->lng->txt('dcl_record_settings'));

        if (!$create) {
            $inputs = $this->setValues($inputs);
        }

        $this->ctrl->setParameter($this, 'table_id', $this->table_id);
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, $create ? 'save' : 'update'),
            $inputs
        );
    }

    private function checkbox(string $label): Checkbox
    {
        return $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('dcl_' . $label),
            $this->lng->txt('dcl_' . $label . '_desc')
        );
    }

    protected function setValues(array $inputs): array
    {
        $inputs['edit'] = $inputs['edit']->withValue([
            'title' => $this->table->getTitle(),
            'description' => $this->table->getDescription(),
            'visible' => $this->table->getIsVisible(),
        ]);
        $sort_field = $this->table->getDefaultSortField();
        $inputs['table'] = $inputs['table']->withValue([
            'default_sort_field' => in_array($sort_field, $this->table->getFieldIds()) ? $sort_field : '',
            'default_sort_field_order' => $this->table->getDefaultSortFieldOrder(),
            'export_enabled' => $this->table->getExportEnabled(),
            'import_enabled' => $this->table->getImportEnabled(),
            'comments_enabled' => $this->table->getPublicCommentsEnabled()
        ]);
        $inputs['record'] = $inputs['record']->withValue([
            'add_perm' => $this->table->getAddPerm() ? ['save_confirmation' => $this->table->getSaveConfirmation()] : null,
            'edit_perm' => $this->table->getEditPerm() ? ($this->table->getEditByOwner() ? 'own' : 'all') : 'none',
            'delete_perm' => $this->table->getDeletePerm() ? ($this->table->getDeleteByOwner() ? 'own' : 'all') : 'none',
            'view_own_records_perm' => $this->table->getViewOwnRecordsPerm(),
            'limited' => $this->table->getLimited() ? ['limit_start' => $this->table->getLimitStart(), 'limit_end' => $this->table->getLimitEnd()] : null
        ]);

        return $inputs;
    }

    public function save(bool $create = true): void
    {
        if (!ilObjDataCollectionAccess::checkActionForObjId('write', $this->obj_id)) {
            return;
        }

        $form = $this->initForm($create)->withRequest($this->http->request());
        $data = $form->getData();

        if ($data !== null) {
            if ($create) {
                $this->table = new ilDclTable();
            }
            foreach (ilObjectFactory::getInstanceByObjId($this->obj_id)->getTables() as $table) {
                if ($table->getTitle() === $data['edit']['title'] && $table->getId() !== $this->table->getId()) {
                    $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('dcl_table_title_unique'));
                    $this->tpl->setContent($this->ui_renderer->render($form));
                    return;
                }
            }

            $this->table->setObjId($this->obj_id);
            $this->table->setTitle($data['edit']['title']);
            $this->table->setDescription($data['edit']['description']);
            $this->table->setIsVisible($data['edit']['visible']);

            $this->table->setExportEnabled($data['table']['export_enabled']);
            $this->table->setImportEnabled($data['table']['import_enabled']);
            $this->table->setPublicCommentsEnabled($data['table']['comments_enabled']);

            $this->table->setAddPerm($data['record']['add_perm'] !== null);
            $this->table->setSaveConfirmation($data['record']['add_perm']['save_confirmation'] ?? false);
            $this->table->setEditPerm($data['record']['edit_perm'] !== 'none');
            $this->table->setEditByOwner($data['record']['edit_perm'] === 'own');
            $this->table->setDeletePerm($data['record']['delete_perm'] !== 'none');
            $this->table->setDeleteByOwner($data['record']['delete_perm'] === 'own');
            $this->table->setViewOwnRecordsPerm($data['record']['view_own_records_perm']);
            $this->table->setLimited($data['record']['limited'] !== null);
            if ($data['record']['limited']['limit_start'] ?? null !== null) {
                $this->table->setLimitStart($data['record']['limited']['limit_start']->format('Y-m-d H:i:s'));
            } else {
                $this->table->setLimitStart('');
            }
            if ($data['record']['limited']['limit_end'] ?? null !== null) {
                $this->table->setLimitEnd($data['record']['limited']['limit_end']->format('Y-m-d H:i:s'));
            } else {
                $this->table->setLimitEnd('');
            }

            if ($create) {
                $this->table->doCreate();
                $this->ctrl->setParameter($this, 'table_id', $this->table->getId());
                $message = 'dcl_msg_table_created';
            } else {
                $this->table->setDefaultSortField($data['table']['default_sort_field']);
                $this->table->setDefaultSortFieldOrder($data['table']['default_sort_field_order']);
                $this->table->doUpdate();
                $message = 'dcl_msg_table_edited';
            }
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt($message), true);
            $this->ctrl->redirectByClass(ilDclTableEditGUI::class, 'edit');
        } else {
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
    }

    public function confirmDelete(): void
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_table'));

        $conf->addItem('table', (string) $this->table->getId(), $this->table->getTitle());

        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');

        $this->tpl->setContent($conf->getHTML());
    }

    public function cancelDelete(): void
    {
        $this->ctrl->redirectByClass("ilDclTableListGUI", "listTables");
    }

    public function delete(): void
    {
        if (count($this->table->getCollectionObject()->getTables()) < 2) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt("dcl_cant_delete_last_table"),
                true
            );
            $this->table->doDelete(true);
        } else {
            $this->table->doDelete();
        }
        $this->ctrl->clearParameterByClass("ilobjdatacollectiongui", "table_id");
        $this->ctrl->redirectByClass("ildcltablelistgui", "listtables");
    }

    public function setAsDefault(): void
    {
        $object = ilObjectFactory::getInstanceByObjId($this->obj_id);
        $order = 20;
        foreach ($object->getTables() as $table) {
            if ($table->getId() === $this->table->getId()) {
                $table->setOrder(10);
            } else {
                $table->setOrder($order);
                $order += 10;
            }
            $table->doUpdate();
        }
        $this->ctrl->redirectByClass(ilDclTableListGUI::class, 'listTables');
    }

    protected function checkAccess(): bool
    {
        $ref_id = $this->parent_object->getDataCollectionObject()->getRefId();

        return $this->table_id ? ilObjDataCollectionAccess::hasAccessToEditTable(
            $ref_id,
            $this->table_id
        ) : ilObjDataCollectionAccess::hasWriteAccess($ref_id);
    }
}
