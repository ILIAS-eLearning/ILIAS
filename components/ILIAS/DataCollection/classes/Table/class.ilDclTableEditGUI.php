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
        if ($cmd === 'save_create') {
            $this->save('create');
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
        $this->tpl->setContent(sprintf($this->lng->txt('dcl_edit_table'), $this->table->getTitle()) . $this->ui_renderer->render($this->initForm('edit')));
    }

    public function cancel(): void
    {
        $this->ctrl->redirectByClass(ilDclTableListGUI::class, 'listTables');
    }

    public function initForm(string $a_mode = 'create'): Form
    {
        $inputs = [];

        $edit = [];
        $edit['title'] = $this->ui_factory->input()->field()->text($this->lng->txt('title'))->withRequired(true);
        if ($a_mode !== 'create') {
            $options = [0 => $this->lng->txt('dcl_please_select')];
            foreach ($this->table->getFields() as $field) {
                if ($field->getId() !== 'comments' && $field->getRecordQuerySortObject() !== null) {
                    $options[$field->getId()] = $field->getTitle();
                }
            }
            $edit['default_sort_field'] = $this->ui_factory->input()->field()->select(
                $this->lng->txt('dcl_default_sort_field'),
                $options,
                $this->lng->txt('dcl_default_sort_field_desc')
            );

            $edit['default_sort_field_order'] = $this->ui_factory->input()->field()->select(
                $this->lng->txt('dcl_default_sort_field_order'),
                ['asc' => $this->lng->txt('dcl_asc'), 'desc' => $this->lng->txt('dcl_desc')],
            );
        }
        $edit['description'] = $this->ui_factory->input()->field()->markdown(
            new ilUIMarkdownPreviewGUI(),
            $this->lng->txt('additional_info'),
            $this->lng->txt('dcl_additional_info_desc')
        );
        $inputs['edit'] = $this->ui_factory->input()->field()->section(
            $edit,
            $this->lng->txt('general_settings')
        );

        $user = [];
        $user['add_perm'] = $this->ui_factory->input()->field()->optionalGroup(
            [
                'save_confirmation' => $this->ui_factory->input()->field()->checkbox($this->lng->txt('dcl_save_confirmation'), $this->lng->txt('dcl_save_confirmation_desc'))
            ],
            $this->lng->txt('dcl_add_perm'),
            $this->lng->txt('dcl_add_perm_desc')
        )->withValue(['save_confirmation' => false]);
        $user['edit_perm'] = $this->ui_factory->input()->field()->radio($this->lng->txt('dcl_edit_perm'))
            ->withOption('all', $this->lng->txt('dcl_all_entries'))
            ->withOption('own', $this->lng->txt('dcl_own_entries'))
            ->withOption('none', $this->lng->txt('dcl_no_entries'))
            ->withValue('own');
        $user['delete_perm'] = $this->ui_factory->input()->field()->radio($this->lng->txt('dcl_delete_perm'))
            ->withOption('all', $this->lng->txt('dcl_all_entries'))
            ->withOption('own', $this->lng->txt('dcl_own_entries'))
            ->withOption('none', $this->lng->txt('dcl_no_entries'))
            ->withValue('own');
        $user['view_own_records_perm'] = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('dcl_view_own_records_perm'),
            $this->lng->txt('dcl_view_own_records_perm_desc'),
        );
        $user['export_enabled'] = $this->ui_factory->input()->field()->checkbox($this->lng->txt('dcl_export_enabled'), $this->lng->txt('dcl_export_enabled_desc'));
        $user['import_enabled'] = $this->ui_factory->input()->field()->checkbox($this->lng->txt('dcl_import_enabled'), $this->lng->txt('dcl_import_enabled_desc'));
        $user['limited'] = $this->ui_factory->input()->field()->optionalGroup(
            [
                'limit_start' => $this->ui_factory->input()->field()->dateTime($this->lng->txt('dcl_limit_start'))->withUseTime(true),
                'limit_end' => $this->ui_factory->input()->field()->dateTime($this->lng->txt('dcl_limit_end'))->withUseTime(true)
            ],
            $this->lng->txt('dcl_limited'),
            $this->lng->txt('dcl_limited_desc')
        )->withValue(null);
        $inputs['user'] = $this->ui_factory->input()->field()->section(
            $user,
            $this->lng->txt('dcl_permissions_form')
        );

        if ($a_mode === 'edit') {
            $inputs = $this->setValues($inputs);
        }

        $this->ctrl->setParameter($this, 'table_id', $this->table_id);
        return $this->ui_factory->input()->container()->form()->standard($this->ctrl->getFormAction($this, $a_mode === 'edit' ? 'save' : 'save_create'), $inputs);
    }

    protected function setValues(array $inputs): array
    {
        $inputs['edit'] = $inputs['edit']->withValue([
            'title' => $this->table->getTitle(),
            'default_sort_field' => $this->table->getDefaultSortField(),
            'default_sort_field_order' => $this->table->getDefaultSortFieldOrder(),
            'description' => $this->table->getDescription(),
        ]);
        $inputs['user'] = $inputs['user']->withValue([
            'add_perm' => $this->table->getAddPerm() ? ['save_confirmation' => $this->table->getSaveConfirmation()] : null,
            'edit_perm' => $this->table->getEditPerm() ? ($this->table->getEditByOwner() ? 'own' : 'all') : 'none',
            'delete_perm' => $this->table->getDeletePerm() ? ($this->table->getDeleteByOwner() ? 'own' : 'all') : 'none',
            'view_own_records_perm' => $this->table->getViewOwnRecordsPerm(),
            'export_enabled' => $this->table->getExportEnabled(),
            'import_enabled' => $this->table->getImportEnabled(),
            'limited' => $this->table->getLimited() ? ['limit_start' => $this->table->getLimitStart(), 'limit_end' => $this->table->getLimitEnd()] : null
        ]);

        return $inputs;
    }

    public function save(string $a_mode = 'edit'): void
    {
        if (!ilObjDataCollectionAccess::checkActionForObjId('write', $this->obj_id)) {
            return;
        }

        $form = $this->initForm($a_mode)->withRequest($this->http->request());
        $data = $form->getData();

        if ($data !== null) {
            if ($a_mode === 'create') {
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
            if ($a_mode !== 'create') {
                $this->table->setDefaultSortField($data['edit']['default_sort_field']);
                $this->table->setDefaultSortFieldOrder($data['edit']['default_sort_field_order']);
            }
            $this->table->setDescription($data['edit']['description']);

            $this->table->setAddPerm($data['user']['add_perm'] !== null);
            $this->table->setSaveConfirmation($data['user']['add_perm']['save_confirmation'] ?? false);
            $this->table->setEditPerm($data['user']['edit_perm'] !== 'none');
            $this->table->setEditByOwner($data['user']['edit_perm'] === 'own');
            $this->table->setDeletePerm($data['user']['delete_perm'] !== 'none');
            $this->table->setDeleteByOwner($data['user']['delete_perm'] === 'own');
            $this->table->setViewOwnRecordsPerm($data['user']['view_own_records_perm']);
            $this->table->setExportEnabled($data['user']['export_enabled']);
            $this->table->setImportEnabled($data['user']['import_enabled']);
            $this->table->setLimited($data['user']['limited'] !== null);
            if ($data['user']['limited']['limit_start'] ?? null !== null) {
                $this->table->setLimitStart($data['user']['limited']['limit_start']->format('Y-m-d H:i:s'));
            } else {
                $this->table->setLimitStart('');
            }
            if ($data['user']['limited']['limit_end'] ?? null !== null) {
                $this->table->setLimitEnd($data['user']['limited']['limit_end']->format('Y-m-d H:i:s'));
            } else {
                $this->table->setLimitEnd('');
            }

            if ($a_mode === 'create') {
                $this->table->doCreate();
                $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('dcl_msg_table_created'), true);
                $this->ctrl->setParameterByClass(ilDclFieldListGUI::class, 'table_id', $this->table->getId());
                $this->ctrl->redirectByClass(ilDclFieldListGUI::class, 'listFields');
            } else {
                $this->table->doUpdate();
                $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('dcl_msg_table_edited'), true);
                $this->ctrl->redirectByClass(ilDclTableEditGUI::class, 'edit');
            }
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
                'failure',
                $this->lng->txt("dcl_cant_delete_last_table"),
                true
            ); //TODO change lng var
            $this->table->doDelete(true);
        } else {
            $this->table->doDelete();
        }
        $this->ctrl->clearParameterByClass("ilobjdatacollectiongui", "table_id");
        $this->ctrl->redirectByClass("ildcltablelistgui", "listtables");
    }

    public function enableVisible(): void
    {
        $this->table->setIsVisible(true);
        $this->table->doUpdate();
        $this->ctrl->redirectByClass(ilDclTableListGUI::class, 'listTables');
    }

    public function disableVisible(): void
    {
        $this->table->setIsVisible(false);
        $this->table->doUpdate();
        $this->ctrl->redirectByClass(ilDclTableListGUI::class, 'listTables');
    }

    public function enableComments(): void
    {
        $this->table->setPublicCommentsEnabled(true);
        $this->table->doUpdate();
        $this->ctrl->redirectByClass(ilDclTableListGUI::class, 'listTables');
    }

    public function disableComments(): void
    {
        $this->table->setPublicCommentsEnabled(false);
        $this->table->doUpdate();
        $this->ctrl->redirectByClass(ilDclTableListGUI::class, 'listTables');
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
