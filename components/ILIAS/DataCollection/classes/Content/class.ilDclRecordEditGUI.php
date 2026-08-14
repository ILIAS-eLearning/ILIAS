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

use ILIAS\HTTP\Services;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\File;
use ILIAS\UI\Component\Input\Field\TreeSelect;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @ilCtrl_Calls ilDclRecordEditGUI: ilDataCollectionUploadHandlerGUI
 */
class ilDclRecordEditGUI
{
    public const int REDIRECT_RECORD_LIST = 1;
    public const int REDIRECT_DETAIL = 2;

    protected ilDclTable $table;
    protected ilDclTableView $tableview;
    protected ilDclBaseRecordModel $record;
    protected readonly ilCtrl $ctrl;
    protected readonly ilLanguage $lng;
    protected readonly ilObjUser $user;
    protected readonly Factory $factory;
    protected readonly ilRbacSystem $rbac;
    protected readonly Renderer $renderer;
    protected readonly Services $http;
    protected readonly ilGlobalTemplateInterface $tpl;
    protected readonly ILIAS\Refinery\Factory $refinery;

    public function __construct(protected ilObjDataCollection $obj, protected int $table_id, protected int $tableview_id)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
        $this->rbac = $DIC->rbac()->system();
        $this->renderer = $DIC->ui()->renderer();
        $this->factory = $DIC->ui()->factory();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();

        if ($this->http->wrapper()->query()->has('record_id')) {
            $record_id = $this->http->wrapper()->query()->retrieve(
                'record_id',
                $this->refinery->kindlyTo()->int()
            );
            $this->record = ilDclCache::getRecordCache($record_id);
        } else {
            $this->record = new ilDclBaseRecordModel();
        }
        $this->table = ilDclCache::getTableCache($this->table_id);
        $this->tableview = ilDclTableView::findOrGetInstance($this->tableview_id);
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        if (strtolower($this->ctrl->getNextClass()) === strtolower(ilDataCollectionUploadHandlerGUI::class)) {
            $this->ctrl->forwardCommand(new ilDataCollectionUploadHandlerGUI());
            return;
        }
        switch ($cmd) {
            case 'create':
                if (ilObjDataCollectionAccess::hasPermissionToAddRecord($this->obj->getRefid(), $this->table->getId())) {
                    global $DIC;
                    $DIC->help()->setSubScreenId('create');
                    $this->tpl->setContent($this->lng->txt('dcl_add_new_record') . $this->renderer->render($this->getForm()));
                    return;
                }
                break;
            case 'edit':
                if ($this->record->getId() !== 0 && $this->record->hasPermissionToEdit($this->obj->getRefid())) {
                    $this->tpl->setContent($this->lng->txt('dcl_update_record') . $this->renderer->render($this->getForm()));
                    return;
                }
                break;
            case 'save':
                if ($this->record->getId() !== 0) {
                    if ($this->record->hasPermissionToEdit($this->obj->getRefid())) {
                        $this->save();
                    }
                } else {
                    if ($this->rbac->checkAccess('add_entry', $this->obj->getRefid())) {
                        $this->save();
                    }
                }
                return;
                break;
            case 'delete':
                if ($this->record->hasPermissionToDelete($this->obj->getRefid())) {
                    $this->record->doDelete();
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_record_deleted'), true);
                    $this->ctrl->redirectByClass(ilDclRecordListGUI::class, 'listRecords');
                }
                break;
            default:
                $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('dcl_msg_no_perm_edit'), true);
        }

        if ($this->http->wrapper()->query()->has('detail')) {
            $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'record_id', $this->record->getId());
            $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'table_id', $this->table->getId());
            $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'tableview_id', $this->tableview->getId());
            $this->ctrl->redirectByClass(ilDclDetailedViewGUI::class, 'renderRecord');
        }

        $this->ctrl->redirectByClass(ilDclRecordListGUI::class, 'listRecords');
    }

    public function save(): void
    {
        $all_fields = $this->table->getRecordFields();

        $create = $this->record->getId() === 0;
        $form = $this->getForm()->withRequest($this->http->request());
        $data = $form->getData();

        $errors = false;
        if ($data !== null) {
            $errors = [];
            foreach ($all_fields as $field) {
                $field_setting = $field->getViewSetting($this->tableview_id);
                if ($field_setting->isVisibleInForm($create) && !$field_setting->isLocked($create)) {
                    try {
                        $field->checkValidity($data[$field->getId()] ?? null, $this->record->getId());
                    } catch (ilDclInputException $e) {
                        $errors[$field->getId()] = $e->getMessage();
                    }
                }
            }

            if (!$create && $this->tableview->getFieldSetting('owner')->isVisibleEdit() && !$this->tableview->getFieldSetting('owner')->isLocked($create)) {
                $owner = ilObjUser::_lookupId($data['owner']);
                if ($owner === null) {
                    $errors['owner'] = $this->lng->txt('user_not_known');
                }
            }
        }
        if ($errors !== []) {
            if (is_array($errors)) {
                $this->tpl->setContent($this->renderer->render($this->getForm($errors)->withRequest($this->http->request())));
            } else {
                $this->tpl->setContent($this->renderer->render($form));
            }
            return;
        }

        $date_obj = new ilDateTime(time(), IL_CAL_UNIX);
        $this->record->setLastUpdate($date_obj);
        $this->record->setLastEditBy($this->user->getId());
        if ($create) {
            $this->record->setTableId($this->table_id);
            $this->record->setOwner($this->user->getId());
            $this->record->setCreateDate($date_obj);
            $this->record->doCreate();
        }

        foreach ($all_fields as $field) {
            $field_setting = $field->getViewSetting($this->tableview_id);
            if ($field_setting->isVisibleInForm($create) && !$field_setting->isLocked($create)) {
                $value = $data[$field->getId()] ?? null;
                if (
                    $field instanceof ilDclFileFieldModel ||
                    $field instanceof ilDclIliasReferenceFieldModel
                ) {
                    $value = $value === [] ? null : $value[0];
                }
                $this->record->getRecordField((int) $field->getId())->setValue($value);
            }
        }

        if ($create) {
            $this->obj->sendRecordNotification(ilDclNotificationType::RECORD_CREATE, $this->record);
        } else {
            if ($this->tableview->getFieldSetting('owner')->isVisibleEdit() && !$this->tableview->getFieldSetting('owner')->isLocked($create)) {
                $this->record->setOwner(ilObjUser::_lookupId($data['owner']));
            }
        }
        $this->record->doUpdate();

        global $DIC;
        $DIC->event()->raise(
            'components/ILIAS/DataCollection',
            $create ? 'crateRecord' : 'updateRecord',
            [
                'dcl' => $this->obj,
                'table_id' => $this->table_id,
                'record_id' => $this->record->getId(),
                'record' => $this->record,
            ]
        );

        $this->ctrl->setParameter($this, "table_id", $this->table_id);
        $this->ctrl->setParameter($this, "tableview_id", $this->tableview_id);
        $this->ctrl->setParameter($this, "record_id", $this->record->getId());

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirectByClass(ilDclRecordListGUI::class, 'listRecords');
    }

    public function getForm(array $errors = []): Form
    {
        $inputs = [];

        $edit = ilObjDataCollectionAccess::hasWriteAccess($this->obj->getRefId());

        $create = $this->record->getId() === 0;
        foreach ($this->table->getRecordFields() as $rfield) {
            $field_setting = $rfield->getViewSetting($this->tableview_id);
            if ($field_setting->isVisibleInForm($create)) {
                $field = ilDclCache::getFieldRepresentation($rfield)->getInputField();
                if ($field !== null) {
                    if ($rfield instanceof ilDclCopyFieldModel && !$create) {
                        $value = $this->record->getRecordFieldValue($rfield->getId());
                        if ($value !== '') {
                            $item = ilDclCache::getFieldRepresentation($rfield)->getInputField($value);
                        }
                    }
                    $field = $field->withDisabled($field_setting->isLocked($create));
                    $field = $field->withRequired($field_setting->isRequired($create));
                    if ($create) {
                        $value = ilDclTableViewBaseDefaultValue::findSingle(
                            $rfield->getDatatypeId(),
                            $rfield->getViewSetting($this->tableview->getId())->getId()
                        )?->getValue();
                    } else {
                        $value = $this->record->getRecordField((int) $rfield->getId())->getValue();
                    }
                    if ($value !== null) {
                        if ($field instanceof File) {
                            $value = [$value];
                        }
                        $field = $field->withValue($value);
                    }
                    if (isset($errors[$rfield->getId()])) {
                        $field = $field->withError($errors[$rfield->getId()]);
                    }
                    $inputs[$rfield->getId()] = $field;
                }
            }
        }

        if (!$create && $this->tableview->getFieldSetting('owner')->isVisibleEdit()) {
            $inputs['owner'] = $this->factory->input()->field()->text($this->lng->txt('dcl_owner'))
                ->withDisabled($this->tableview->getFieldSetting('owner')->isLocked($create))
                ->withRequired(true)
                ->withValue(ilObjUser::_lookupName($this->record->getOwner())['login']);
        }

        $this->ctrl->setParameter($this, 'record_id', $this->record->getId());
        return $this->factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'save'),
            $inputs
        );
    }
}
