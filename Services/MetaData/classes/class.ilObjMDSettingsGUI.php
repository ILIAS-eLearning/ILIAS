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

use ILIAS\Refinery\Factory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Data\URI;

/**
 * Meta Data Settings.
 * @author       Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilObjMDSettingsGUI: ilPermissionGUI, ilAdvancedMDSettingsGUI, ilMDCopyrightUsageGUI
 * @ingroup      ServicesMetaData
 */
class ilObjMDSettingsGUI extends ilObjectGUI
{
    protected ?ilPropertyFormGUI $form = null;
    protected ?ilMDSettings $md_settings = null;
    protected ?ilMDCopyrightSelectionEntry $entry = null;
    protected GlobalHttpState $http;
    protected Factory $refinery;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;

    public function __construct($a_data, $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        global $DIC;
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->type = 'mds';
        $this->lng->loadLanguageModule("meta");
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    protected function initEntryIdFromQuery(): int
    {
        $entry_id = 0;
        if ($this->http->wrapper()->query()->has('entry_id')) {
            $entry_id = $this->http->wrapper()->query()->retrieve(
                'entry_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return $entry_id;
    }

    protected function initEntryIdFromPost(): array
    {
        $entries = [];
        if ($this->http->wrapper()->post()->has('entry_id')) {
            return $this->http->wrapper()->post()->retrieve(
                'entry_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'iladvancedmdsettingsgui':
                $this->tabs_gui->setTabActive('md_advanced');
                $adv_md = new ilAdvancedMDSettingsGUI(
                    ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION,
                    $this->ref_id
                );
                $ret = $this->ctrl->forwardCommand($adv_md);
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');

                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilmdcopyrightusagegui':
                // this command is used if copyrightUsageGUI calls getParentReturn (see ...UsageGUI->setTabs)
                $this->ctrl->setReturn($this, 'showCopyrightSettings');
                $copyright_id = $this->initEntryIdFromQuery();
                $gui = new ilMDCopyrightUsageGUI($copyright_id);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->initMDSettings();
                if (!$cmd || $cmd === 'view') {
                    $cmd = "showGeneralSettings";
                }

                $this->$cmd();
                break;
        }
    }

    protected function getType(): string
    {
        return $this->type;
    }

    protected function getParentObjType(): string
    {
        return 'meta';
    }

    protected function getAdministrationFormId(): int
    {
        return ilAdministrationSettingsFormHandler::FORM_META_COPYRIGHT;
    }

    public function getAdminTabs(): void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "md_general_settings",
                $this->ctrl->getLinkTarget($this, "showGeneralSettings"),
                array("showGeneralSettings", "view")
            );

            $this->tabs_gui->addTarget(
                "md_copyright",
                $this->ctrl->getLinkTarget($this, "showCopyrightSettings"),
                array("showCopyrightSettings")
            );

            $this->tabs_gui->addTarget(
                "md_advanced",
                $this->ctrl->getLinkTargetByClass('iladvancedmdsettingsgui', ""),
                '',
                'iladvancedmdsettingsgui'
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function showGeneralSettings(?ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initGeneralSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function initGeneralSettingsForm(string $a_mode = "edit"): ilPropertyFormGUI
    {
        $this->tabs_gui->setTabActive('md_general_settings');
        $form = new ilPropertyFormGUI();
        $ti = new ilTextInputGUI($this->lng->txt("md_delimiter"), "delimiter");
        $ti->setInfo($this->lng->txt("md_delimiter_info"));
        $ti->setMaxLength(1);
        $ti->setSize(1);
        $ti->setValue($this->md_settings->getDelimiter());
        $form->addItem($ti);

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton("saveGeneralSettings", $this->lng->txt("save"));
            $form->addCommandButton("showGeneralSettings", $this->lng->txt("cancel"));
        }
        $form->setTitle($this->lng->txt("md_general_settings"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        return $form;
    }

    public function saveGeneralSettings(): void
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->ctrl->redirect($this, "showGeneralSettings");
        }
        $form = $this->initGeneralSettingsForm();
        if ($form->checkInput()) {
            $delim = $form->getInput('delimiter');
            $delim = (
                trim($delim) === '' ?
                ',' :
                trim($delim)
            );
            $this->md_settings->setDelimiter($delim);
            $this->md_settings->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, "showGeneralSettings");
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $form->setValuesByPost();
        $this->showGeneralSettings($form);
    }

    public function showCopyrightSettings(?ilPropertyFormGUI $form = null): void
    {
        $this->tabs_gui->setTabActive('md_copyright');
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.settings.html', 'Services/MetaData');

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->tpl->setVariable('SETTINGS_TABLE', $form->getHTML());

        $has_write = $this->access->checkAccess('write', '', $this->object->getRefId());
        $table_gui = new ilMDCopyrightTableGUI($this, 'showCopyrightSettings', $has_write);
        $table_gui->setTitle($this->lng->txt("md_copyright_selection"));
        $table_gui->parseSelections();

        if ($has_write) {
            $table_gui->addCommandButton('addEntry', $this->lng->txt('add'));
            $table_gui->addMultiCommand("confirmDeleteEntries", $this->lng->txt("delete"));
            $table_gui->setSelectAllCheckbox("entry_id");
        }
        $this->tpl->setVariable('COPYRIGHT_TABLE', $table_gui->getHTML());
    }

    public function saveCopyrightSettings(): void
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->ctrl->redirect($this, "showCopyrightSettings");
        }
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->md_settings->activateCopyrightSelection((bool) $form->getInput('active'));
            $this->md_settings->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $this->showCopyrightSettings($form);
    }

    public function showCopyrightUsages(): void
    {
        $this->ctrl->setParameterByClass('ilmdcopyrightusagegui', 'entry_id', $this->initEntryIdFromQuery());
        $this->ctrl->redirectByClass('ilmdcopyrightusagegui', "showUsageTable");
    }

    public function editEntry(?StandardForm $form = null): void
    {
        $this->ctrl->saveParameter($this, 'entry_id');
        if (!$form instanceof StandardForm) {
            $form = $this->initCopyrightEditForm();
        }
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $post_url = $this->ctrl->getLinkTarget($this, 'showCopyrightSettings')
        );
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    public function addEntry(?StandardForm $form = null): void
    {
        if (!$form instanceof StandardForm) {
            $form = $this->initCopyrightEditForm('add');
        }
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $post_url = $this->ctrl->getLinkTarget($this, 'showCopyrightSettings')
        );
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    public function saveEntry(): bool
    {
        $form = $this->initCopyrightEditForm('add')->withRequest($this->request);
        if ($data = $form->getData()) {
            $data = $data[0];
            $this->entry = new ilMDCopyrightSelectionEntry(0);
            $this->entry->setTitle($data['title']);
            $this->entry->setDescription($data['description']);
            $this->entry->setCopyrightData(
                $data['copyright']['full_name'],
                $data['copyright']['link'],
                $data['copyright']['image_link'],
                $data['copyright']['alt_text']
            );
            $this->entry->setOutdated((bool) $data['outdated']);
            $this->entry->setLanguage('en');
            $this->entry->setCopyrightAndOtherRestrictions(true);
            $this->entry->setCosts(false);

            if (!$this->entry->validate()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('fill_out_all_required_fields'));
                $this->addEntry($form);
                return false;
            }
            $this->entry->add();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
            return true;
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $this->addEntry($form);
        return false;
    }

    public function confirmDeleteEntries(): void
    {
        $entry_ids = $this->initEntryIdFromPost();
        if (!count($entry_ids)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->showCopyrightSettings();
            return;
        }

        $c_gui = new ilConfirmationGUI();
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteEntries"));
        $c_gui->setHeaderText($this->lng->txt("md_delete_cp_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "showCopyrightSettings");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteEntries");

        // add items to delete
        foreach ($entry_ids as $entry_id) {
            $entry = new ilMDCopyrightSelectionEntry($entry_id);
            $c_gui->addItem('entry_id[]', (string) $entry_id, $entry->getTitle());
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    public function deleteEntries(): bool
    {
        $entry_ids = $this->initEntryIdFromPost();
        if (!count($entry_ids)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->showCopyrightSettings();
            return true;
        }

        foreach ($entry_ids as $entry_id) {
            $entry = new ilMDCopyrightSelectionEntry($entry_id);
            $entry->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_copyrights_deleted'));
        $this->showCopyrightSettings();
        return true;
    }

    public function updateEntry(): bool
    {
        $this->entry = new ilMDCopyrightSelectionEntry($this->initEntryIdFromQuery());
        $form = $this->initCopyrightEditForm()->withRequest($this->request);
        if ($data = $form->getData()) {
            $data = $data[0];
            $this->entry->setTitle($data['title']);
            $this->entry->setDescription($data['description']);
            $this->entry->setCopyrightData(
                $data['copyright']['full_name'],
                $data['copyright']['link'],
                $data['copyright']['image_link'],
                $data['copyright']['alt_text']
            );
            $this->entry->setOutdated((bool) $data['outdated']);
            if (!$this->entry->validate()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('fill_out_all_required_fields'));
                $this->editEntry($form);
                return false;
            }
            $this->entry->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
            return true;
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->editEntry($form);
        return false;
    }

    protected function initSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('md_copyright_settings'));

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('saveCopyrightSettings', $this->lng->txt('save'));
            $form->addCommandButton('showCopyrightSettings', $this->lng->txt('cancel'));
        }

        $check = new ilCheckboxInputGUI($this->lng->txt('md_copyright_enabled'), 'active');
        $check->setChecked($this->md_settings->isCopyrightSelectionActive());
        $check->setValue('1');
        $check->setInfo($this->lng->txt('md_copyright_enable_info'));
        $form->addItem($check);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            $this->getAdministrationFormId(),
            $form,
            $this
        );
        return $form;
    }

    public function initCopyrightEditForm(string $a_mode = 'edit'): StandardForm
    {
        if (!is_object($this->entry)) {
            $this->entry = new ilMDCopyrightSelectionEntry($this->initEntryIdFromQuery());
        }

        $inputs = [];
        $ff = $this->ui_factory->input()->field();

        $title = $ff
            ->text($this->lng->txt('title'))
            ->withValue($this->entry->getTitle())
            ->withRequired(true)
            ->withMaxLength(255);
        $inputs['title'] = $title;

        $des = $ff
            ->textarea($this->lng->txt('description'))
            ->withValue($this->entry->getDescription());
        $inputs['description'] = $des;

        $usage = $ff
            ->radio($this->lng->txt('meta_copyright_usage'))
            ->withOption('0', $this->lng->txt('meta_copyright_in_use'))
            ->withOption('1', $this->lng->txt('meta_copyright_outdated'))
            ->withValue((int) $this->entry->getOutdated());
        $inputs['outdated'] = $usage;

        $cp_data = $this->entry->getCopyrightData();

        $full_name = $ff
            ->text($this->lng->txt('md_copyright_full_name'))
            ->withValue($cp_data->fullName());

        $link = $ff
            ->url(
                $this->lng->txt('md_copyright_link'),
                $this->lng->txt('md_copyright_link_info')
            )
            ->withValue((string) $cp_data->link())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(fn ($v) => $v instanceof URI ? $v : null)
            );

        $image_link = $ff
            ->url($this->lng->txt('md_copyright_image_link'))
            ->withValue((string) $cp_data->imageLink())
            ->withAdditionalTransformation($this->refinery->custom()->transformation(
                fn ($v) => $v instanceof URI ? $v : null
            ));

        $alt_text = $ff
            ->text(
                $this->lng->txt('md_copyright_alt_text'),
                $this->lng->txt('md_copyright_alt_text_info')
            )
            ->withValue($cp_data->altText());

        $cop = $ff
            ->section(
                [
                    'full_name' => $full_name,
                    'link' => $link,
                    'image_link' => $image_link,
                    'alt_text' => $alt_text
                ],
                $this->lng->txt('md_copyright_value')
            );
        $inputs['copyright'] = $cop;

        $form_title = '';
        $post_url = '';
        switch ($a_mode) {
            case 'edit':
                $form_title = $this->lng->txt('md_copyright_edit');
                $post_url = $this->ctrl->getLinkTarget($this, 'updateEntry');
                break;

            case 'add':
                $form_title = $this->lng->txt('md_copyright_add');
                $post_url = $this->ctrl->getLinkTarget($this, 'saveEntry');
                break;
        }
        return $this->ui_factory->input()->container()->form()->standard(
            $post_url,
            [$ff->section($inputs, $form_title)]
        );
    }

    protected function initMDSettings(): void
    {
        $this->md_settings = ilMDSettings::_getInstance();
    }

    public function saveCopyrightPosition(): bool
    {
        if (!$this->http->wrapper()->post()->has('order')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_select_one'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
            return false;
        }
        $positions = $this->http->wrapper()->post()->retrieve(
            'order',
            $this->refinery->kindlyTo()->dictOf(
                $this->refinery->kindlyTo()->string()
            )
        );
        asort($positions);
        $position = 0;
        foreach ($positions as $entry_id => $position_ignored) {
            $copyright = new ilMDCopyrightSelectionEntry($entry_id);
            $copyright->setOrderPosition($position++);
            $copyright->update();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->ctrl->redirect($this, 'showCopyrightSettings');
        return false;
    }
}
