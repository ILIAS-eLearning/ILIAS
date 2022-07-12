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
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;

    const P_ENTRY_ID = 'entry_id';
    const CMD_SHOW_CONTENT = 'showContent';
    const CMD_SEND_FILE = "sendFile";
    const TAB_CONTENT = "content";
    const SUB_TAB_FILTER = "filter";
    const CMD_VIEW = "view";
    const TAB_EXPORT = "export";
    const TAB_SETTINGS = self::SUBTAB_SETTINGS;
    const TAB_ID_RECORDS = "id_records";
    const TAB_ID_PERMISSIONS = "id_permissions";
    const TAB_ID_INFO = "id_info";
    const CMD_SHOW_DETAILS = "showDetails";
    const CMD_EDIT = "edit";
    const SUBTAB_SETTINGS = "settings";
    const CMD_EDIT_OBJECT = 'editObject';
    const CMD_UPDATE_OBJECT = 'updateObject';
    
    public ?ilObject $object = null;
    protected ?\ilBiblFactoryFacade $facade = null;
    protected \ilBiblTranslationFactory $translation_factory;
    protected \ilBiblFieldFactory $field_factory;
    protected \ilBiblFieldFilterFactory $filter_factory;
    protected \ilBiblTypeFactory $type_factory;
    /**
     * @var Services
     */
    protected $storage;
    protected \ilObjBibliographicStakeholder $stakeholder;
    protected ?string $cmd = self::CMD_SHOW_CONTENT;

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;

        $this->storage = $DIC['resource_storage'];
        $this->stakeholder = new ilObjBibliographicStakeholder();

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
    public function getStandardCmd() : string
    {
        return self::CMD_VIEW;
    }

    /**
     * getType
     * @deprecated REFACTOR use type factory via Facade
     */
    public function getType() : string
    {
        return "bibl";
    }

    /**
     * executeCommand
     */
    public function executeCommand() : void
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
            default:
                $this->prepareOutput();
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW_CONTENT);
                switch ($cmd) {
                    case 'edit':
                    case 'update':
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
    public function infoScreen() : void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass(ilInfoScreenGUI::class);
        $this->infoScreenForward();
    }

    /**
     * show information screen
     */
    public function infoScreenForward() : void
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
    public function addLocatorItems() : void
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
    public static function _goto(string $a_target) : void
    {
        global $DIC;

        $id = explode("_", $a_target);
        $DIC->ctrl()->setTargetScript('ilias.php');
        $DIC->ctrl()->setParameterByClass(ilObjBibliographicGUI::class, "ref_id", $id[0]);
        // Detail-View
        if ($id[1]) {
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
    protected function initCreationForms(string $a_new_type) : array
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

    public function save() : void
    {
        global $DIC;

        $form = $this->initCreationForms($this->getType());
        if ($form[self::CFORM_NEW]->checkInput()) {
            parent::save();
        } else {
            $form = $form[self::CFORM_NEW];
            $form->setValuesByPost();
            $DIC->ui()->mainTemplate()->setContent($form->getHtml());
        }
    }

    protected function afterSave(ilObject $a_new_object) : void
    {
        $this->addNews($a_new_object->getId(), 'created');
        $this->ctrl->redirect($this, self::CMD_EDIT);
    }

    /**
     * setTabs
     * create tabs (repository/workspace switch)
     * this had to be moved here because of the context-specific permission tab
     */
    public function setTabs() : void
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

    protected function initSubTabs() : void
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
    public function editObject() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs_gui;
        $ilErr = $this->error;

        if (!$this->checkPermissionBool("write")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $ilTabs->activateTab("settings");

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values !== []) {
            $form->setValuesByArray($values, true);
        }

        $this->addExternalEditFormCustom($form);

        $tpl->setContent($form->getHTML());
    }

    public function initEditForm() : \ilPropertyFormGUI
    {
        global $DIC;

        $form = parent::initEditForm();
        // Add File-Upload
        $in_file = new ilFileInputGUI(
            $DIC->language()
                ->txt("bibliography_file"),
            "bibliographic_file"
        );
        $in_file->setSuffixes(array("ris", "bib", "bibtex"));
        $in_file->setRequired(false);
        $cb_override = new ilCheckboxInputGUI(
            $DIC->language()
                ->txt("override_entries"),
            "override_entries"
        );
        $cb_override->addSubItem($in_file);

        $form->addItem($cb_override);

        $section_appearance = new ilFormSectionHeaderGUI();
        $section_appearance->setTitle($this->lng->txt('cont_presentation'));
        $form->addItem($section_appearance);
        $DIC->object()->commonSettings()->legacyForm($form, $this->object)->addTileImage();

        $form->setFormAction($DIC->ctrl()->getFormAction($this, "save"));

        return $form;
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form) : void
    {
        global $DIC;

        $DIC->tabs()->activateTab(self::SUBTAB_SETTINGS);
        // is_online
        $cb = new ilCheckboxInputGUI($DIC->language()->txt("online"), "is_online");
        $a_form->addItem($cb);
    }

    public function getEditFormCustomValues(array &$values) : void
    {
        $values["is_online"] = $this->object->getOnline();
    }

    public function render() : void
    {
        $this->showContent();
    }

    /**
     * shows the overview page with all entries in a table
     */
    public function showContent() : void
    {
        global $DIC;

        // if user has read permission and object is online OR user has write permissions
        $read_access = $DIC->access()->checkAccess('read', "", $this->object->getRefId());
        $online = $this->object->getOnline();
        $write_access = $DIC->access()->checkAccess('write', "", $this->object->getRefId());
        if (($read_access && $online) || $write_access) {
            $DIC->tabs()->activateTab(self::TAB_CONTENT);

            $b = ilLinkButton::getInstance();
            $b->setCaption('download_original_file');
            $b->setUrl($DIC->ctrl()->getLinkTargetByClass(self::class, self::CMD_SEND_FILE));
            $b->setPrimary(true);
            $DIC->toolbar()->addButtonInstance($b);

            $table = new ilBiblEntryTableGUI($this, $this->facade);
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

    protected function applyFilter() : void
    {
        $table = new ilBiblEntryTableGUI($this, $this->facade);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    protected function resetFilter() : void
    {
        $table = new ilBiblEntryTableGUI($this, $this->facade);
        $table->resetFilter();
        $table->resetOffset();
        $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    /**
     * provide file as a download
     */
    public function sendFile() : void
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

    public function showDetails() : void
    {
        global $DIC;

        if ($DIC->access()->checkAccess('read', "", $this->object->getRefId())) {
            $id = $DIC->http()->request()->getQueryParams()[self::P_ENTRY_ID];
            $entry = $this->facade->entryFactory()
                                  ->findByIdAndTypeString($id, $this->object->getFileTypeAsString());
            $bibGUI = new ilBiblEntryDetailPresentationGUI($entry, $this->facade);

            $DIC->ui()->mainTemplate()->setContent($bibGUI->getHTML());
        } else {
            $this->handleNonAccess();
        }
    }

    public function view() : void
    {
        $this->showContent();
    }

    /**
     * updateSettings
     */
    public function updateCustom(ilPropertyFormGUI $a_form) : void
    {
        global $DIC;

        if ($DIC->access()->checkAccess('write', "", $this->object->getRefId())) {
            if ($this->object->getOnline() != $a_form->getInput("is_online")) {
                $this->object->setOnline($a_form->getInput("is_online"));
            }

            if (!empty($_FILES['bibliographic_file']['name'])) {
                $this->addNews($this->object->getId(), 'updated');
            }

            $DIC->object()->commonSettings()->legacyForm($a_form, $this->object)->saveTileImage();
        } else {
            $this->handleNonAccess();
        }
    }

    public function toggleNotification() : void
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

    public function addNews(int $obj_id, string $change = 'created') : void
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
    public function addToDesk() : void
    {
        $this->addToDeskObject();
    }

    /**
     * Remove from desktop. Alias for removeFromDeskObject.
     * @access public
     */
    public function removeFromDesk() : void
    {
        $this->removeFromDeskObject();
    }

    protected function afterImport(ilObject $a_new_object) : void
    {
        /**
         * @var $a_new_object ilObjBibliographic
         */
        $a_new_object->parseFileToDatabase();

        parent::afterImport($a_new_object);
    }

    private function handleNonAccess() : void
    {
        global $DIC;

        $this->tpl->setOnScreenMessage('failure', $DIC->language()->txt("no_permission"), true);
        ilObjectGUI::_gotoRepositoryRoot();
    }
}
