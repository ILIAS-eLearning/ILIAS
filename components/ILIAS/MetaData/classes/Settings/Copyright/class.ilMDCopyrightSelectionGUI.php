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
use ILIAS\UI\Component\Modal\RoundTrip;

/**
 * @ilCtrl_Calls ilMDCopyrightSelectionGUI: ilMDCopyrightUsageGUI
 */
class ilMDCopyrightSelectionGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs_gui;
    protected ilToolbarGUI $toolbar_gui;
    protected GlobalHttpState $http;
    protected Factory $refinery;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;

    protected ilObjMDSettingsGUI $parent_gui;
    protected ilMDSettingsAccessService $access_service;
    protected ilMDSettingsModalService $modal_service;

    protected ?ilMDSettings $md_settings = null;
    protected ?ilMDCopyrightSelectionEntry $entry = null;

    public function __construct(ilObjMDSettingsGUI $parent_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs_gui = $DIC->tabs();
        $this->toolbar_gui = $DIC->toolbar();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->parent_gui = $parent_gui;
        $this->access_service = new ilMDSettingsAccessService(
            $this->parent_gui->getRefId(),
            $DIC->access()
        );
        $this->modal_service = new ilMDSettingsModalService(
            $this->ui_factory
        );

        $this->lng->loadLanguageModule("meta");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (
            !$this->access_service->hasCurrentUserVisibleAccess() ||
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilMDCopyrightUsageGUI::class):
                // this command is used if copyrightUsageGUI calls getParentReturn (see ...UsageGUI->setTabs)
                $this->ctrl->setReturn($this, 'showCopyrightSelection');
                $copyright_id = $this->initEntryIdFromQuery();
                $gui = new ilMDCopyrightUsageGUI($copyright_id);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'showCopyrightSettings';
                }

                $this->$cmd();
                break;
        }
    }

    protected function setCopyrightTabs(string $active_subtab): void
    {
        if (
            !$this->access_service->hasCurrentUserVisibleAccess() ||
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
            return;
        }
        $this->tabs_gui->setTabActive('md_copyright');

        $this->tabs_gui->addSubTab(
            'md_copyright_settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTarget($this, 'showCopyrightSettings')
        );

        $this->tabs_gui->addSubTab(
            'md_copyright_selection',
            $this->lng->txt('md_copyright_selection'),
            $this->ctrl->getLinkTarget($this, 'showCopyrightSelection')
        );

        if (in_array($active_subtab, [
            'md_copyright_settings',
            'md_copyright_selection'
        ])) {
            $this->tabs_gui->activateSubTab($active_subtab);
            return;
        }
        $this->tabs_gui->activateSubTab('md_copyright_settings');
    }

    public function showCopyrightSettings(?ilPropertyFormGUI $form = null): void
    {
        $this->setCopyrightTabs('md_copyright_settings');

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function showCopyrightSelection(): void
    {
        $this->setCopyrightTabs('md_copyright_selection');

        $has_write = $this->access_service->hasCurrentUserWriteAccess();
        $table_gui = new ilMDCopyrightTableGUI($this, 'showCopyrightSelection', $has_write);
        $table_gui->setTitle($this->lng->txt("md_copyright_selection"));
        $table_gui->parseSelections();

        if ($has_write) {
            $add_modal = $this->modal_service->placeholderModal(
                $link = $this->ctrl->getLinkTarget($this, 'addEntry', '', true)
            );
            $this->toolbar_gui->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('md_copyright_add'),
                $add_modal->getShowSignal()
            ));

            $table_gui->addMultiCommand("confirmDeleteEntries", $this->lng->txt("delete"));
            $table_gui->setSelectAllCheckbox("entry_id");
        }

        $this->modal_service->initJS($this->tpl);
        $this->tpl->setContent(
            $table_gui->getHTML() .
            (isset($add_modal) ? $this->ui_renderer->render($add_modal) : '') .
            $table_gui->getRenderedModals()
        );
    }

    public function saveCopyrightSettings(): void
    {
        if (!$this->access_service->hasCurrentUserWriteAccess()) {
            $this->ctrl->redirect($this, "showCopyrightSettings");
        }
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->MDSettings()->activateCopyrightSelection((bool) $form->getInput('active'));
            $this->MDSettings()->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showCopyrightSettings');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $this->showCopyrightSettings($form);
    }

    public function editEntry(?RoundTrip $modal = null): void
    {
        $this->ctrl->saveParameter($this, 'entry_id');
        if (!$modal instanceof RoundTrip) {
            $modal = $this->initCopyrightEditModal();
        }
        echo $this->ui_renderer->renderAsync($modal);
        exit;
    }

    public function addEntry(?RoundTrip $modal = null): void
    {
        if (!$modal instanceof RoundTrip) {
            $modal = $this->initCopyrightEditModal('add');
        }
        echo $this->ui_renderer->renderAsync($modal);
        exit;
    }

    public function saveEntry(): void
    {
        $modal = $this
            ->initCopyrightEditModal('add')
            ->withRequest($this->http->request());

        if ($data = $modal->getData()) {
            $this->entry = new ilMDCopyrightSelectionEntry(0);
            $this->entry->setTitle($data['title']);
            $this->entry->setDescription($data['description']);
            $this->entry->setCopyright($data['copyright']);
            $this->entry->setLanguage('en');
            $this->entry->setCopyrightAndOtherRestrictions(true);
            $this->entry->setCosts(false);
            $this->entry->setOutdated((bool) $data['outdated']);
            if (!$this->entry->validate()) {
                throw new ilException($this->lng->txt('fill_out_all_required_fields'));
            }
            $this->entry->add();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);

            $link = $this->ctrl->getLinkTarget($this, 'showCopyrightSelection');
            echo $this->modal_service->redirectHTML($link);
            exit;
        }
        $this->addEntry($modal);
    }

    public function confirmDeleteEntries(): void
    {
        $entry_ids = $this->initEntryIdFromPost();
        if (!count($entry_ids)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->showCopyrightSelection();
            return;
        }

        $c_gui = new ilConfirmationGUI();
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteEntries"));
        $c_gui->setHeaderText($this->lng->txt("md_delete_cp_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "showCopyrightSelection");
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
            $this->showCopyrightSelection();
            return true;
        }

        foreach ($entry_ids as $entry_id) {
            $entry = new ilMDCopyrightSelectionEntry($entry_id);
            $entry->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_copyrights_deleted'));
        $this->showCopyrightSelection();
        return true;
    }

    public function updateEntry(): void
    {
        $this->entry = new ilMDCopyrightSelectionEntry($this->initEntryIdFromQuery());
        $modal = $this
            ->initCopyrightEditModal()
            ->withRequest($this->http->request());

        if ($data = $modal->getData()) {
            $this->entry->setTitle($data['title']);
            $this->entry->setDescription($data['description']);
            $this->entry->setCopyright($data['copyright']);
            $this->entry->setOutdated((bool) $data['outdated']);
            if (!$this->entry->validate()) {
                throw new ilException($this->lng->txt('fill_out_all_required_fields'));
            }
            $this->entry->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);

            $link = $this->ctrl->getLinkTarget($this, 'showCopyrightSelection');
            echo $this->modal_service->redirectHTML($link);
            exit;
        }
        $this->editEntry($modal);
    }

    protected function initCopyrightEditModal(string $a_mode = 'edit'): RoundTrip
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

        $cop = $ff
            ->textarea($this->lng->txt('md_copyright_value'))
            ->withValue($this->entry->getCopyright());
        $inputs['copyright'] = $cop;

        $usage = $ff
            ->radio($this->lng->txt('meta_copyright_usage'))
            ->withOption('0', $this->lng->txt('meta_copyright_in_use'))
            ->withOption('1', $this->lng->txt('meta_copyright_outdated'))
            ->withValue((int) $this->entry->getOutdated());
        $inputs['outdated'] = $usage;

        switch ($a_mode) {
            case 'add':
                $title = $this->lng->txt('md_copyright_add');
                $post_url = $this->ctrl->getLinkTarget($this, 'saveEntry', '', true);
                break;

            case 'edit':
            default:
                $title = $this->lng->txt('md_copyright_edit');
                $this->ctrl->setParameter($this, 'entry_id', $this->entry->getEntryId());
                $post_url = $this->ctrl->getLinkTarget($this, 'updateEntry', '', true);
                $this->ctrl->clearParameters($this);
                break;
        }

        return $this->modal_service->modalWithForm(
            $title,
            $post_url,
            ...$inputs
        );
    }

    protected function initSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('md_copyright_settings'));

        if ($this->access_service->hasCurrentUserWriteAccess()) {
            $form->addCommandButton('saveCopyrightSettings', $this->lng->txt('save'));
        }

        $check = new ilCheckboxInputGUI($this->lng->txt('md_copyright_enabled'), 'active');
        $check->setChecked($this->MDSettings()->isCopyrightSelectionActive());
        $check->setValue('1');
        $check->setInfo($this->lng->txt('md_copyright_enable_info'));
        $form->addItem($check);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            $this->getAdministrationFormId(),
            $form,
            $this->parent_gui
        );
        return $form;
    }

    public function saveCopyrightPosition(): bool
    {
        if (!$this->http->wrapper()->post()->has('order')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_select_one'), true);
            $this->ctrl->redirect($this, 'showCopyrightSelection');
            return false;
        }
        $positions = $this->http->wrapper()->post()->retrieve(
            'order',
            $this->refinery->kindlyTo()->dictOf(
                $this->refinery->kindlyTo()->int()
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
        $this->ctrl->redirect($this, 'showCopyrightSelection');
        return false;
    }

    protected function MDSettings(): ilMDSettings
    {
        if (!isset($this->md_settings)) {
            $this->md_settings = ilMDSettings::_getInstance();
        }
        return $this->md_settings;
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

    protected function getAdministrationFormId(): int
    {
        return ilAdministrationSettingsFormHandler::FORM_META_COPYRIGHT;
    }
}
