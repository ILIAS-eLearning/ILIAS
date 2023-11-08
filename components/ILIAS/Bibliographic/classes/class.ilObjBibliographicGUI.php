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

use ILIAS\ResourceStorage\Services;
use ILIAS\DI\Container;
use ILIAS\FileUpload\MimeType;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\ResourceStorage\Revision\RevisionCollection;

/**
 * Class ilObjBibliographicGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Gabriel Comte <gc@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @author            Thibeau Fuhrer <thf@studer-raimann.ch>
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilInfoScreenGUI, ilNoteGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilPermissionGUI, ilObjectCopyGUI, ilExportGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilObjUserGUI, ilBiblEntryPresentationGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilBiblEntryTableGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilBiblFieldFilterGUI
 * @ilCtrl_isCalledBy ilObjBibliographicGUI: ilRepositoryGUI
 */
class ilObjBibliographicGUI extends ilObject2GUI implements ilDesktopItemHandling
{
    use \ILIAS\components\OrgUnit\ARHelper\DIC;

    public const P_ENTRY_ID = 'entry_id';
    public const CMD_SHOW_CONTENT = 'showContent';
    public const CMD_SEND_FILE = "sendFile";
    public const TAB_CONTENT = "content";
    public const SUB_TAB_FILTER = "filter";
    public const CMD_VIEW = "view";
    public const TAB_EXPORT = "export";
    public const TAB_SETTINGS = self::SUBTAB_SETTINGS;
    public const TAB_ID_RECORDS = "id_records";
    public const TAB_ID_PERMISSIONS = "id_permissions";
    public const TAB_ID_INFO = "id_info";
    public const CMD_SHOW_DETAILS = "showDetails";
    public const SUBTAB_SETTINGS = "settings";
    public const CMD_EDIT_OBJECT = 'editObject';
    public const CMD_UPDATE_OBJECT = "updateObject";
    public const CMD_SETTINGS = "settings";
    public const CMD_OVERWRITE_BIBLIOGRAPHIC_FILE = "overwriteBibliographicFile";
    public const CMD_REPLACE_BIBLIOGRAPHIC_FILE = "replaceBibliographicFile";
    public const SECTION_REPLACE_BIBLIOGRAPHIC_FILE = 'section_replace_bibliographic_file';
    public const PROP_BIBLIOGRAPHIC_FILE = "bibliographic_file";
    public const SECTION_EDIT_BIBLIOGRAPHY = 'section_edit_bibliography';
    public const PROP_TITLE_AND_DESC = 'title_and_desc';
    public const SECTION_AVAILABILITY = 'section_availability';
    public const PROP_ONLINE_STATUS = 'online_status';
    public const SECTION_PRESENTATION = 'section_presentation';
    public const PROP_TILE_IMAGE = 'tile_image';

    public ?ilObject $object = null;
    protected ?\ilBiblFactoryFacade $facade = null;
    protected \ilBiblTranslationFactory $translation_factory;
    protected \ilBiblFieldFactory $field_factory;
    protected \ilBiblFieldFilterFactory $filter_factory;
    protected \ilBiblTypeFactory $type_factory;

    protected ilHelpGUI $help;
    protected Services $storage;
    protected \ilObjBibliographicStakeholder $stakeholder;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\Refinery\Factory $refinery;
    protected ?string $cmd = self::CMD_SHOW_CONTENT;

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;

        $this->help = $DIC['ilHelp'];
        $this->storage = $DIC['resource_storage'];
        $this->stakeholder = new ilObjBibliographicStakeholder();
        $this->http = $DIC->http();
        $this->ui_factory = $DIC->ui()->factory();
        $this->refinery = $DIC->refinery();

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        $DIC->language()->loadLanguageModule('bibl');
        $DIC->language()->loadLanguageModule('content');
        $DIC->language()->loadLanguageModule('obj');
        $DIC->language()->loadLanguageModule('cntr');

        if (is_object($this->object)) {
            /** @var ilObjBibliographic $obj */
            $obj = $this->object;
            $this->facade = new ilBiblFactoryFacade($obj);
        }
    }

    /**
     * getStandardCmd
     */
    public function getStandardCmd(): string
    {
        return self::CMD_VIEW;
    }

    /**
     * getType
     * @deprecated REFACTOR use type factory via Facade
     */
    public function getType(): string
    {
        return "bibl";
    }

    /**
     * executeCommand
     */
    public function executeCommand(): void
    {
        global $DIC;
        $ilNavigationHistory = $DIC['ilNavigationHistory'];

        // Navigation History
        $link = $this->dic()->ctrl()->getLinkTarget($this, $this->getStandardCmd());
        if ($this->object != null) {
            $ilNavigationHistory->addItem($this->object->getRefId(), $link, "bibl");
            $this->addHeaderAction();
        }

        // general Access Check, especially for single entries not matching the object
        if ($this->object instanceof ilObjBibliographic && !$DIC->access()->checkAccess(
            'read',
            "",
            $this->object->getRefId()
        )) {
            $this->handleNonAccess();
        }

        $next_class = $this->dic()->ctrl()->getNextClass($this);
        $this->cmd = $this->dic()->ctrl()->getCmd();
        switch ($next_class) {
            case strtolower(ilInfoScreenGUI::class):
                $this->prepareOutput();
                $this->dic()->tabs()->activateTab(self::TAB_ID_INFO);
                $this->infoScreenForward();
                break;
            case strtolower(ilCommonActionDispatcherGUI::class):
                $this->prepareOutput();
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            case strtolower(ilPermissionGUI::class):
                $this->prepareOutput();
                $this->dic()->tabs()->activateTab(self::TAB_ID_PERMISSIONS);
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;
            case strtolower(ilObjectCopyGUI::class):
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('bibl');
                $this->dic()['tpl']->loadStandardTemplate();
                $this->ctrl->forwardCommand($cp);
                break;
            case strtolower(ilExportGUI::class):
                $this->prepareOutput();
                $this->dic()->tabs()->setTabActive(self::TAB_EXPORT);
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $this->ctrl->forwardCommand($exp_gui);
                break;
            case strtolower(ilBiblFieldFilterGUI::class):
                $this->prepareOutput();
                $this->dic()->tabs()->setTabActive(self::TAB_SETTINGS);
                $this->initSubTabs();
                $this->tabs_gui->activateSubTab(self::SUB_TAB_FILTER);
                $this->ctrl->forwardCommand(new ilBiblFieldFilterGUI($this->facade));
                break;
            case strtolower(ilObjBibliographicUploadHandlerGUI::class):
                $rid = "";
                if ($this->object && $this->object->getResourceId()) {
                    $rid = $this->object->getResourceId()->serialize();
                }
                $this->ctrl->forwardCommand(new ilObjBibliographicUploadHandlerGUI($rid));
                break;
            default:
                $this->prepareOutput();
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW_CONTENT);
                switch ($cmd) {
                    case self::CMD_SETTINGS:
                    case self::CMD_EDIT_OBJECT:
                    case self::CMD_UPDATE_OBJECT:
                        $this->initSubTabs();
                        $this->tabs_gui->activateSubTab(self::SUBTAB_SETTINGS);
                        $this->{$cmd}();
                        break;
                    default:
                        $this->{$cmd}();
                        break;
                }
                break;
        }
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function infoScreen(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass(ilInfoScreenGUI::class);
        $this->infoScreenForward();
    }

    /**
     * show information screen
     */
    public function infoScreenForward(): void
    {
        global $DIC;
        /**
         * @var $DIC Container
         */

        if (!$this->checkPermissionBoolAndReturn("visible") && !$this->checkPermissionBoolAndReturn('read')) {
            $this->tpl->setOnScreenMessage('failure', $DIC['lng']->txt("msg_no_perm_read"), true);
            $this->ctrl->redirectByClass('ilDashboardGUI', '');
        }
        $DIC->tabs()->activateTab(self::TAB_ID_INFO);
        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        // Storage Info
        $irss = new ilResourceStorageInfoGUI($this->object->getResourceId());
        $irss->append($info);

        $this->ctrl->forwardCommand($info);
    }

    /*
     * addLocatorItems
     */
    public function addLocatorItems(): void
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];
        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
        }
    }

    /**
     * _goto
     * Deep link
     */
    public static function _goto(string $a_target): void
    {
        global $DIC;

        $id = explode("_", $a_target);
        $DIC->ctrl()->setTargetScript('ilias.php');
        $DIC->ctrl()->setParameterByClass(ilObjBibliographicGUI::class, "ref_id", (int) ($id[0] ?? 1));
        // Detail-View
        if (isset($id[1]) && $id[1] !== '') {
            $DIC->ctrl()
                ->setParameterByClass(ilObjBibliographicGUI::class, ilObjBibliographicGUI::P_ENTRY_ID, $id[1]);
            $DIC->ctrl()->redirectByClass(
                array(
                    ilRepositoryGUI::class,
                    ilObjBibliographicGUI::class,
                ),
                self::CMD_SHOW_DETAILS
            );
        } else {
            $DIC->ctrl()->redirectByClass(
                array(
                    ilRepositoryGUI::class,
                    ilObjBibliographicGUI::class,
                ),
                self::CMD_VIEW
            );
        }
    }

    /**
     * @return mixed[]
     */
    protected function initCreationForms(string $a_new_type): array
    {
        global $DIC;

        $forms = parent::initCreationForms($a_new_type);
        // Add File-Upload
        $in_file = new ilFileInputGUI($DIC->language()->txt("bibliography_file"), "bibliographic_file");
        $in_file->setSuffixes(array("ris", "bib", "bibtex"));
        $in_file->setRequired(true);
        $forms[self::CFORM_NEW]->addItem($in_file);
        $this->ctrl->saveParameterByClass('ilobjrootfoldergui', 'new_type');
        $forms[self::CFORM_NEW]->setFormAction($this->ctrl->getFormActionByClass('ilobjrootfoldergui', "save"));

        return $forms;
    }

    public function save(): void
    {
        $form = $this->initCreationForms($this->getType());
        if ($form[self::CFORM_NEW]->checkInput()) {
            parent::save();
        } else {
            $form = $form[self::CFORM_NEW];
            $form->setValuesByPost();
            $this->ui()->mainTemplate()->setContent($form->getHtml());
        }
    }

    public function updateObject(): void
    {
        $form = $this->getSettingsForm();
        $form = $form->withRequest($this->http->request());
        $result = $form->getInputGroup()->getContent();

        if (!$result->isOK()) {
            $this->tpl->setOnScreenMessage('failure', $result->error(), true);
            $this->tpl->setContent(
                $this->ui()->renderer()->render([$form])
            );
        } else {
            $values = $result->value();

            $this->object->getObjectProperties()->storePropertyTitleAndDescription(
                $values[self::SECTION_EDIT_BIBLIOGRAPHY][self::PROP_TITLE_AND_DESC]
            );
            $this->object->getObjectProperties()->storePropertyIsOnline(
                $values[self::SECTION_AVAILABILITY][self::PROP_ONLINE_STATUS]
            );
            $this->object->getObjectProperties()->storePropertyTileImage(
                $values[self::SECTION_PRESENTATION][self::PROP_TILE_IMAGE]
            );

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('changes_saved'), true);
            $this->ctrl->redirect($this, self::CMD_SETTINGS);
        }
    }

    protected function afterSave(ilObject $a_new_object): void
    {
        $this->addNews($a_new_object->getId(), 'created');
        $this->ctrl->redirect($this, self::CMD_EDIT_OBJECT);
    }

    protected function settings(): void
    {
        $this->tpl->setContent($this->ui()->renderer()->render($this->getSettingsForm()));
    }


    public function overwriteBibliographicFile(): void
    {
        $this->tabs()->clearTargets();
        $this->tabs()->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_CONTENT));
        $this->tpl->setContent($this->ui()->renderer()->render($this->getReplaceBibliographicFileForm()));
    }

    public function replaceBibliographicFile(): void
    {
        $form = $this->getReplaceBibliographicFileForm();
        $form = $form->withRequest($this->http->request());
        $data = $form->getData();
        if ($data !== null && $bibl_file_rid = $this->storage->manage()->find($data[self::SECTION_REPLACE_BIBLIOGRAPHIC_FILE][self::PROP_BIBLIOGRAPHIC_FILE][0])) {
            /**
             * @var $bibl_obj ilObjBibliographic
             */
            $bibl_obj = $this->getObject();
            $bibl_filename = $this->storage->manage()->getResource($bibl_file_rid)->getCurrentRevision()->getTitle();
            $bibl_filetype = $bibl_obj->determineFileTypeByFileName($bibl_filename);

            $bibl_obj->setResourceId($bibl_file_rid);
            $bibl_obj->setFilename($bibl_filename);
            $bibl_obj->setFileType($bibl_filetype);
            $bibl_obj->update();
            $bibl_obj->parseFileToDatabase();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('changes_saved'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
        }

        $this->tpl->setContent(
            $this->ui()->renderer()->render([$form])
        );
    }


    protected function getReplaceBibliographicFileForm(): Standard
    {
        /**
        * @var $bibl_obj ilObjBibliographic
         */
        $bibl_obj = $this->getObject();
        $rid = $bibl_obj->getResourceId() ? $bibl_obj->getResourceId()->serialize() : "";
        $bibl_upload_handler = new ilObjBibliographicUploadHandlerGUI($rid);

        $max_filesize_bytes = ilFileUtils::getUploadSizeLimitBytes();
        $max_filesize_mb = round($max_filesize_bytes / 1024 / 1024, 1);
        $info_file_limitations = $this->lng->txt('file_notice') . " " . number_format($max_filesize_mb, 1) . " MB <br>"
            . $this->lng->txt('file_allowed_suffixes') . " .bib, .bibtex, .ris";
        $section_replace_bibliographic_file = $this->ui_factory->input()->field()->section(
            [
                self::PROP_BIBLIOGRAPHIC_FILE => $this->ui_factory->input()->field()->file(
                    $bibl_upload_handler,
                    $this->lng->txt('bibliography_file'),
                    $info_file_limitations
                )->withMaxFileSize($max_filesize_bytes)
                 ->withRequired(true)
                 ->withAdditionalTransformation(
                     $this->getValidBiblFileSuffixConstraint()
                 )
            ],
            $this->lng->txt('replace_bibliography_file'),
            $this->lng->txt('replace_bibliography_file_info')
        );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_REPLACE_BIBLIOGRAPHIC_FILE),
            [
                self::SECTION_REPLACE_BIBLIOGRAPHIC_FILE => $section_replace_bibliographic_file
            ]
        );
    }

    protected function getValidBiblFileSuffixConstraint(): \ILIAS\Refinery\Constraint
    {
        return $this->refinery->custom()->constraint(
            function ($bibl_file_input): bool {
                global $DIC;
                $rid = $bibl_file_input[0];
                $resource_identifier = $DIC->resourceStorage()->manage()->find($rid);
                if ($resource_identifier !== null) {
                    $bibl_file = $DIC->resourceStorage()->manage()->getCurrentRevision($resource_identifier);
                    $bibl_file_suffix = $bibl_file->getInformation()->getSuffix();
                    if (in_array($bibl_file_suffix, ['ris', 'bib', 'bibtex'])) {
                        return true;
                    }
                }
                return false;
            },
            $this->lng->txt('msg_error_invalid_bibl_file_suffix')
        );
    }

    /**
     * setTabs
     * create tabs (repository/workspace switch)
     * this had to be moved here because of the context-specific permission tab
     */
    public function setTabs(): void
    {
        global $DIC;

        $ilHelp = $DIC['ilHelp'];
        /**
         * @var $ilHelp      ilHelpGUI
         */
        $ilHelp->setScreenIdComponent('bibl');
        // info screen
        if ($DIC->access()->checkAccess('read', "", $this->object->getRefId())) {
            $DIC->tabs()->addTab(
                self::TAB_CONTENT,
                $DIC->language()
                    ->txt(self::TAB_CONTENT),
                $this->ctrl->getLinkTarget($this, self::CMD_SHOW_CONTENT)
            );
        }
        // info screen
        if ($DIC->access()->checkAccess('visible', "", $this->object->getRefId())
            || $DIC->access()->checkAccess('read', "", $this->object->getRefId())
        ) {
            $DIC->tabs()->addTab(
                self::TAB_ID_INFO,
                $DIC->language()
                    ->txt("info_short"),
                $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary")
            );
        }
        // settings
        if ($DIC->access()->checkAccess('write', "", $this->object->getRefId())) {
            $DIC->tabs()->addTab(
                self::SUBTAB_SETTINGS,
                $DIC->language()
                    ->txt(self::SUBTAB_SETTINGS),
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_OBJECT)
            );
        }
        // export
        if ($DIC->access()->checkAccess("write", "", $this->object->getRefId())) {
            $DIC->tabs()->addTab(
                self::TAB_EXPORT,
                $DIC->language()
                    ->txt(self::TAB_EXPORT),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }
        // edit permissions
        if ($DIC->access()->checkAccess('edit_permission', "", $this->object->getRefId())) {
            $DIC->tabs()->addTab(
                self::TAB_ID_PERMISSIONS,
                $DIC->language()
                    ->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm")
            );
        }
    }

    protected function initSubTabs(): void
    {
        global $DIC;
        $DIC->tabs()->addSubTab(
            self::SUBTAB_SETTINGS,
            $DIC->language()
                ->txt(self::SUBTAB_SETTINGS),
            $this->ctrl->getLinkTarget($this, self::CMD_EDIT_OBJECT)
        );
        $DIC->tabs()->addSubTab(
            self::SUB_TAB_FILTER,
            $DIC->language()
                ->txt("bibl_filter"),
            $this->ctrl->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_STANDARD)
        );
    }

    /**
     * edit object
     * @access    public
     */
    public function editObject(): void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->error->raiseError($this->lng->txt("msg_no_perm_write"), $this->error->MESSAGE);
        }

        $this->tabs_gui->activateTab("settings");
        $form = $this->getSettingsForm();
        $this->tpl->setContent($this->ui()->renderer()->render($form));
    }

    public function getSettingsForm(): Standard
    {
        $field_factory = $this->ui_factory->input()->field();

        $section_edit_bibliography = $field_factory->section(
            [
                self::PROP_TITLE_AND_DESC => $this->object->getObjectProperties()->getPropertyTitleAndDescription()->toForm(
                    $this->lng,
                    $field_factory,
                    $this->refinery
                )
            ],
            $this->lng->txt('bibl_edit'),
            ''
        );
        $section_availability = $field_factory->section(
            [
                self::PROP_ONLINE_STATUS => $this->object->getObjectProperties()->getPropertyIsOnline()->toForm(
                    $this->lng,
                    $field_factory,
                    $this->refinery
                )
            ],
            $this->lng->txt('rep_activation_availability'),
            ''
        );
        $section_presentation = $field_factory->section(
            [
                self::PROP_TILE_IMAGE => $this->object->getObjectProperties()->getPropertyTileImage()->toForm(
                    $this->lng,
                    $field_factory,
                    $this->refinery
                )
            ],
            $this->lng->txt('settings_presentation_header'),
            ''
        );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_UPDATE_OBJECT),
            [
                self::SECTION_EDIT_BIBLIOGRAPHY => $section_edit_bibliography,
                self::SECTION_AVAILABILITY => $section_availability,
                self::SECTION_PRESENTATION => $section_presentation
            ]
        );
    }

    public function render(): void
    {
        $this->showContent();
    }

    /**
     * shows the overview page with all entries in a table
     */
    public function showContent(): void
    {
        global $DIC;

        // if user has read permission and object is online OR user has write permissions
        $read_access = $DIC->access()->checkAccess('read', "", $this->object->getRefId());
        $online = $this->object->getObjectProperties()->getPropertyIsOnline()->getIsOnline();
        $write_access = $DIC->access()->checkAccess('write', "", $this->object->getRefId());
        if (($read_access && $online) || $write_access) {
            $DIC->tabs()->activateTab(self::TAB_CONTENT);

            $btn_download_original_file = $this->ui()->factory()->button()->primary(
                $this->lng->txt('download_original_file'),
                $this->ctrl()->getLinkTargetByClass(self::class, self::CMD_SEND_FILE)
            );
            $this->toolbar->addComponent($btn_download_original_file);

            $btn_overwrite_bibliographic_file = $this->ui()->factory()->button()->standard(
                $this->lng->txt('replace_bibliography_file'),
                $this->ctrl()->getLinkTargetByClass(self::class, self::CMD_OVERWRITE_BIBLIOGRAPHIC_FILE)
            );
            $this->toolbar->addComponent($btn_overwrite_bibliographic_file);

            $table = new ilBiblEntryTableGUI($this, $this->facade, $this->ui());
            $html = $table->getHTML();
            $DIC->ui()->mainTemplate()->setContent($html);

            //Permanent Link
            $DIC->ui()->mainTemplate()->setPermanentLink("bibl", $this->object->getRefId());
        } else {
            $object_title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->ref_id));
            $this->tpl->setOnScreenMessage('failure', sprintf(
                $DIC->language()
                    ->txt("msg_no_perm_read_item"),
                $object_title
            ), true);
            //redirect to repository without any parameters
            $this->handleNonAccess();
        }
    }

    protected function applyFilter(): void
    {
        $table = new ilBiblEntryTableGUI($this, $this->facade, $this->ui());
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    protected function resetFilter(): void
    {
        $table = new ilBiblEntryTableGUI($this, $this->facade, $this->ui());
        $table->resetFilter();
        $table->resetOffset();
        $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    /**
     * provide file as a download
     */
    public function sendFile(): void
    {
        global $DIC;

        if ($DIC['ilAccess']->checkAccess('read', "", $this->object->getRefId())) {
            if (!$this->object->isMigrated()) {
                $file_path = $this->object->getLegacyAbsolutePath();
                if ($file_path) {
                    if (is_file($file_path)) {
                        ilFileDelivery::deliverFileAttached(
                            $file_path,
                            $this->object->getFilename(),
                            'application/octet-stream'
                        );
                    } else {
                        $this->tpl->setOnScreenMessage('failure', $DIC['lng']->txt("file_not_found"));
                        $this->showContent();
                    }
                }
            } else {
                $this->storage->consume()->download($this->object->getResourceId())->run();
            }
        } else {
            $this->handleNonAccess();
        }
    }

    public function showDetails(): void
    {
        global $DIC;

        if ($DIC->access()->checkAccess('read', "", $this->object->getRefId())) {
            $id = $DIC->http()->request()->getQueryParams()[self::P_ENTRY_ID];
            $entry = $this->facade->entryFactory()
                                  ->findByIdAndTypeString($id, $this->object->getFileTypeAsString());
            $bibGUI = new ilBiblEntryDetailPresentationGUI($entry, $this->facade, $this->ctrl(), $this->help, $this->lng(), $this->tpl(), $this->tabs(), $this->ui());

            $DIC->ui()->mainTemplate()->setContent($bibGUI->getHTML());
        } else {
            $this->handleNonAccess();
        }
    }

    public function view(): void
    {
        $this->showContent();
    }

    public function toggleNotification(): void
    {
        global $DIC;
        $ntf = $DIC->http()->wrapper()->query()->retrieve(
            "ntf",
            $DIC->refinery()->to()->int()
        );

        switch ($ntf) {
            case 1:
                ilNotification::setNotification(
                    ilNotification::TYPE_DATA_COLLECTION,
                    $DIC->user()
                        ->getId(),
                    $this->obj_id,
                    false
                );
                break;
            case 2:
                ilNotification::setNotification(
                    ilNotification::TYPE_DATA_COLLECTION,
                    $DIC->user()
                        ->getId(),
                    $this->obj_id,
                    true
                );
                break;
        }
        $DIC->ctrl()->redirect($this, "");
    }

    public function addNews(int $obj_id, string $change = 'created'): void
    {
        global $DIC;

        $ilNewsItem = new ilNewsItem();
        $ilNewsItem->setTitle($DIC->language()->txt('news_title_' . $change));
        $ilNewsItem->setPriority(NEWS_NOTICE);
        $ilNewsItem->setContext($obj_id, $this->getType());
        $ilNewsItem->setUserId($DIC->user()->getId());
        $ilNewsItem->setVisibility(NEWS_USERS);
        $ilNewsItem->setContentTextIsLangVar(false);
        $ilNewsItem->create();
    }

    /**
     * Add desktop item. Alias for addToDeskObject.
     * @access public
     */
    public function addToDesk(): void
    {
        $this->addToDeskObject();
    }

    /**
     * Remove from desktop. Alias for removeFromDeskObject.
     * @access public
     */
    public function removeFromDesk(): void
    {
        $this->removeFromDeskObject();
    }

    protected function afterImport(ilObject $a_new_object): void
    {
        /**
         * @var $a_new_object ilObjBibliographic
         */
        $a_new_object->parseFileToDatabase();

        parent::afterImport($a_new_object);
    }

    private function handleNonAccess(): void
    {
        global $DIC;

        $this->tpl->setOnScreenMessage('failure', $DIC->language()->txt("no_permission"), true);
        ilObjectGUI::_gotoRepositoryRoot();
    }
}
