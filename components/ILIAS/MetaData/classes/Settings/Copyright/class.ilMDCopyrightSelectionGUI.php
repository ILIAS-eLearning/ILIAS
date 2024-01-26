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

use ILIAS\Refinery\Factory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\MetaData\Copyright\Renderer;
use ILIAS\MetaData\Copyright\DatabaseRepository;
use ILIAS\Data\URI;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\FileUpload\MimeType;
use ILIAS\ResourceStorage\Services as IRSS;

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
    protected IRSS $irss;

    protected ilObjMDSettingsGUI $parent_gui;
    protected ilMDSettingsAccessService $access_service;
    protected ilMDSettingsModalService $modal_service;
    protected RendererInterface $renderer;
    protected RepositoryInterface $repository;

    protected ?ilMDSettings $md_settings = null;

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
        $this->irss = $DIC->resourceStorage();

        $this->parent_gui = $parent_gui;
        $this->access_service = new ilMDSettingsAccessService(
            $this->parent_gui->getRefId(),
            $DIC->access()
        );
        $this->modal_service = new ilMDSettingsModalService(
            $this->ui_factory
        );
        $this->renderer = new Renderer(
            $DIC->ui()->factory(),
            $DIC->resourceStorage()
        );
        $this->repository = new DatabaseRepository($DIC->database());

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
                $entry = $this->repository->getEntry($this->initEntryIdFromQuery());
                $gui = new ilMDCopyrightUsageGUI($entry);
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilMDCopyrightImageUploadHandlerGUI::class):
                $entry = $this->repository->getEntry($this->initEntryIdFromQuery());
                $file_id = empty($entry?->copyrightData()?->imageFile()) ? '' : $entry?->copyrightData()?->imageFile();
                $handler = new ilMDCopyrightImageUploadHandlerGUI($file_id);
                $this->ctrl->forwardCommand($handler);

                // no break
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

    public function showCopyrightSelection(
        int $current_id = 0,
        RoundTrip $current_modal = null
    ): void {
        $this->setCopyrightTabs('md_copyright_selection');

        $has_write = $this->access_service->hasCurrentUserWriteAccess();

        $table_gui = new ilMDCopyrightTableGUI($this, 'showCopyrightSelection', $has_write);
        $table_gui->setTitle($this->lng->txt("md_copyright_selection"));
        $table_gui->parseSelections();

        $edit_modals = [];
        if ($has_write) {
            foreach ($this->repository->getAllEntries() as $entry) {
                if ($entry->id() === $current_id) {
                    $modal = $current_modal;
                } else {
                    $modal = $this->initCopyrightEditModal($entry);
                }
                $table_gui->setEditModalSignal($entry->id(), $modal->getShowSignal());
                $edit_modals[] = $modal;
            }
            if ($current_id === 0 && !is_null($current_modal)) {
                $add_modal = $current_modal;
            } else {
                $add_modal = $this->initCopyrightEditModal();
            }
            $this->toolbar_gui->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('md_copyright_add'),
                $add_modal->getShowSignal()
            ));

            $table_gui->addMultiCommand("confirmDeleteEntries", $this->lng->txt("delete"));
            $table_gui->setSelectAllCheckbox("entry_id");
        }

        $this->tpl->setContent(
            $table_gui->getHTML() .
            (isset($add_modal) ? $this->ui_renderer->render($add_modal) : '') .
            $this->ui_renderer->render($edit_modals)
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

    public function saveEntry(): void
    {
        $modal = $this
            ->initCopyrightEditModal(null, true)
            ->withRequest($this->http->request());

        if ($data = $modal->getData()) {
            $this->repository->createEntry(
                $data['title'],
                $data['description'],
                (bool) $data['outdated'],
                $data['copyright']['full_name'],
                $data['copyright']['link'],
                $this->extractImageFromData($data),
                $data['copyright']['alt_text']
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);

            $this->ctrl->redirect($this, 'showCopyrightSelection');
        }
        $this->showCopyrightSelection(0, $modal);
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
            $entry = $this->repository->getEntry($entry_id);
            $c_gui->addItem('entry_id[]', (string) $entry_id, $entry->title());
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
            $entry = $this->repository->getEntry($entry_id);
            $this->deleteFile($entry);
            $this->repository->deleteEntry($entry_id);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_copyrights_deleted'));
        $this->showCopyrightSelection();
        return true;
    }

    public function updateEntry(): void
    {
        $entry = $this->repository->getEntry($this->initEntryIdFromQuery());
        $modal = $this
            ->initCopyrightEditModal($entry, true)
            ->withRequest($this->http->request());

        if ($data = $modal->getData()) {
            $this->deleteFileIfChanged($entry, $data);
            $this->repository->updateEntry(
                $entry->id(),
                $data['title'],
                $data['description'],
                (bool) $data['outdated'],
                $data['copyright']['full_name'],
                $data['copyright']['link'],
                $this->extractImageFromData($data),
                $data['copyright']['alt_text']
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);

            $this->ctrl->redirect($this, 'showCopyrightSelection');
        }
        $this->showCopyrightSelection($entry->id(), $modal);
    }

    protected function initCopyrightEditModal(
        EntryInterface $entry = null,
        bool $open_on_load = false
    ): RoundTrip {
        $inputs = [];
        $ff = $this->ui_factory->input()->field();

        $title = $ff
            ->text($this->lng->txt('title'))
            ->withValue($entry?->title() ?? '')
            ->withRequired(true)
            ->withMaxLength(255);
        $inputs['title'] = $title;

        $des = $ff
            ->textarea($this->lng->txt('description'))
            ->withValue($entry?->description() ?? '');
        $inputs['description'] = $des;

        $usage = $ff
            ->radio($this->lng->txt('meta_copyright_usage'))
            ->withOption('0', $this->lng->txt('meta_copyright_in_use'))
            ->withOption('1', $this->lng->txt('meta_copyright_outdated'))
            ->withValue((int) $entry?->isOutdated());
        $inputs['outdated'] = $usage;

        $cp_data = $entry?->copyrightData();

        $full_name = $ff
            ->text($this->lng->txt('md_copyright_full_name'))
            ->withValue($cp_data?->fullName() ?? '');

        $link = $ff
            ->url(
                $this->lng->txt('md_copyright_link'),
                $this->lng->txt('md_copyright_link_info')
            )
            ->withValue((string) $cp_data?->link())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(fn($v) => $v instanceof URI ? $v : null)
            );

        $image_link = $ff
            ->url($this->lng->txt('md_copyright_image_link'))
            ->withValue((string) $cp_data?->imageLink())
            ->withAdditionalTransformation($this->refinery->custom()->transformation(
                fn($v) => $v instanceof URI ? $v : null
            ));

        $file_id = empty($cp_data?->imageFile()) ? '' : $cp_data?->imageFile();
        $image_file = $ff
            ->file(
                new ilMDCopyrightImageUploadHandlerGUI($file_id),
                $this->lng->txt('md_copyright_image_file')
            )
            ->withMaxFiles(1)
            ->withAcceptedMimeTypes([MimeType::IMAGE__PNG, MimeType::IMAGE__JPEG]);
        if ($file_id !== '') {
            $image_file = $image_file->withValue([$file_id]);
        };

        $image_value = 'link_group';
        if (!is_null($cp_data) && !$cp_data->isImageLink()) {
            $image_value = 'file_group';
        }
        $image = $ff
            ->switchableGroup(
                [
                    'link_group' => $ff->group(
                        ['image_link' => $image_link],
                        $this->lng->txt('md_copyright_image_is_link')
                    ),
                    'file_group' => $ff->group(
                        ['image_file' => $image_file],
                        $this->lng->txt('md_copyright_image_is_file')
                    ),
                ],
                $this->lng->txt('md_copyright_image')
            )
            ->withValue($image_value);

        $alt_text = $ff
            ->text(
                $this->lng->txt('md_copyright_alt_text'),
                $this->lng->txt('md_copyright_alt_text_info')
            )
            ->withValue($cp_data?->altText() ?? '');

        $cop = $ff
            ->section(
                [
                    'full_name' => $full_name,
                    'link' => $link,
                    'image' => $image,
                    'alt_text' => $alt_text
                ],
                $this->lng->txt('md_copyright_value')
            );
        $inputs['copyright'] = $cop;

        if (!isset($entry)) {
            $title = $this->lng->txt('md_copyright_add');
            $post_url = $this->ctrl->getLinkTarget($this, 'saveEntry');
        } else {
            $title = $this->lng->txt('md_copyright_edit');
            $this->ctrl->setParameter($this, 'entry_id', $entry->id());
            $post_url = $this->ctrl->getLinkTarget($this, 'updateEntry');
            $this->ctrl->clearParameters($this);
        }

        $modal = $this->modal_service->modalWithForm(
            $title,
            $post_url,
            ...$inputs
        );
        if ($open_on_load) {
            $modal = $modal->withOnLoad($modal->getShowSignal());
        }
        return $modal;
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
                $this->refinery->kindlyTo()->string()
            )
        );
        asort($positions);
        $ids = [];
        foreach ($positions as $entry_id => $position_ignored) {
            $ids[] = (int) $entry_id;
        }
        $this->repository->reorderEntries(...$ids);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->ctrl->redirect($this, 'showCopyrightSelection');
        return false;
    }

    protected function deleteFile(EntryInterface $entry): void
    {
        if (($image_file = $entry->copyrightData()->imageFile()) === '') {
            return;
        }
        if ($id = $this->irss->manage()->find($image_file)) {
            $this->irss->manage()->remove($id, new ilMDCopyrightImageStakeholder());
        }
    }

    protected function deleteFileIfChanged(
        EntryInterface $entry,
        array $data
    ): void {
        if (($image_file = $entry->copyrightData()->imageFile()) === '') {
            return;
        }
        if (
            $data['copyright']['image'][0] === 'file_group' &&
            $image_file === ($data['copyright']['image'][1]['image_file'][0] ?? '')
        ) {
            return;
        }
        $this->deleteFile($entry);
    }

    protected function extractImageFromData(array $data): string|URI
    {
        $v = $data['copyright']['image'];
        if ($v[0] === 'link_group') {
            return empty($link = $v[1]['image_link']) ? '' : $link;
        }
        if ($v[0] === 'file_group') {
            return $v[1]['image_file'][0] ?? '';
        }
        return '';
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
