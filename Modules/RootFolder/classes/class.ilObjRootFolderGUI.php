<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\RootFolder\StandardGUIRequest;

/**
 * Class ilObjRootFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilObjRootFolderGUI: ilPermissionGUI, ilContainerPageGUI
 * @ilCtrl_Calls ilObjRootFolderGUI: ilColumnGUI, ilObjectCopyGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjRootFolderGUI: ilCommonActionDispatcherGUI, ilObjectTranslationGUI
 * @ilCtrl_Calls ilObjRootFolderGUI: ilRepositoryTrashGUI
 */
class ilObjRootFolderGUI extends ilContainerGUI
{
    protected StandardGUIRequest $root_request;
    protected ilHelpGUI $help;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->type = "root";
        $lng = $DIC->language();

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        
        $lng->loadLanguageModule("cntr");
        $lng->loadLanguageModule("obj");

        $this->root_request = $DIC
            ->rootFolder()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->help = $DIC->help();
    }

    protected function getTabs() : void
    {
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $help = $this->help;

        $help->setScreenIdComponent("root");

        $this->ctrl->setParameter($this, "ref_id", $this->ref_id);

        if ($rbacsystem->checkAccess('read', $this->ref_id)) {
            $this->tabs_gui->addTab(
                'view_content',
                $lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "")
            );
        }
        
        if ($rbacsystem->checkAccess('write', $this->ref_id)) {
            $cmd = $this->ctrl->getCmd();
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                "edit",
                get_class($this),
                "",
                $cmd === 'edit'
            );
        }

        // parent tabs (all container: edit_permission, clipboard, trash
        parent::getTabs();
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case strtolower(ilRepositoryTrashGUI::class):
                $ru = new ilRepositoryTrashGUI($this);
                $this->ctrl->setReturn($this, 'trash');
                $this->ctrl->forwardCommand($ru);
                break;

            // container page editing
            case "ilcontainerpagegui":
                $this->prepareOutput(false);
                $ret = $this->forwardToPageObject();
                if ($ret !== "") {
                    $this->tpl->setContent($ret);
                }
                break;

            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilcolumngui":
                $this->checkPermission("read");
                $this->prepareOutput();
                $this->content_style_gui->addCss(
                    $this->tpl,
                    $this->object->getRefId()
                );
                $this->renderObject();
                break;

            case 'ilobjectcopygui':
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('root');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->setTitleAndDescription();
                $this->showContainerPageTabs();
                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;
            
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                $this->setEditTabs("settings_trans");
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;

            default:
                if ($cmd === "infoScreen") {
                    $this->checkPermission("visible");
                } else {
                    try {
                        $this->checkPermission("read");
                    } catch (ilObjectException $exception) {
                        $this->ctrl->redirectToURL("login.php?client_id=" . CLIENT_ID . "&cmd=force_login");
                    }
                }
                $this->prepareOutput();
                $this->content_style_gui->addCss(
                    $this->tpl,
                    $this->object->getRefId()
                );

                if (!$cmd) {
                    $cmd = "render";
                }

                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }
    
    public function renderObject() : void
    {
        global $ilTabs;

        ilObjectListGUI::prepareJsLinks(
            "",
            "",
            $this->ctrl->getLinkTargetByClass(["ilcommonactiondispatchergui", "iltagginggui"], "", "", true, false)
        );
        
        $ilTabs->activateTab("view_content");
        parent::renderObject();
    }

    /**
     * @throws ilObjectException
     */
    public function viewObject() : void
    {
        $this->checkPermission('read');

        if (strtolower($this->root_request->getBaseClass()) === "iladministrationgui") {
            parent::viewObject();
        }

        $this->renderObject();
    }

    protected function setTitleAndDescription() : void
    {
        global $lng;

        parent::setTitleAndDescription();
        $this->tpl->setDescription("");
        if (!ilContainer::_lookupContainerSetting($this->object->getId(), "hide_header_icon_and_title")) {
            if ($this->object->getTitle() === "ILIAS") {
                $this->tpl->setTitle($lng->txt("repository"));
            } elseif ($this->object->getDescription() !== "") {
                $this->tpl->setDescription($this->object->getDescription()); // #13479
            }
        }
    }

    protected function setEditTabs(
        string $active_tab = "settings_misc"
    ) : void {
        $this->tabs_gui->addSubTab(
            "settings_misc",
            $this->lng->txt("settings"),
            $this->ctrl->getLinkTarget($this, "edit")
        );

        $this->tabs_gui->addSubTab(
            "settings_trans",
            $this->lng->txt("obj_multilinguality"),
            $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "")
        );


        $this->tabs_gui->activateTab("settings");
        $this->tabs_gui->activateSubTab($active_tab);
    }
    
    protected function initEditForm() : ilPropertyFormGUI
    {
        $this->setEditTabs();
        $obj_service = $this->getObjectService();

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("repository"));

        // presentation
        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('obj_presentation'));
        $form->addItem($pres);

        // list presentation
        $form = $this->initListPresentationForm($form);

        $this->initSortingForm(
            $form,
            [
                    ilContainer::SORT_TITLE,
                    ilContainer::SORT_CREATION,
                    ilContainer::SORT_MANUAL
            ]
        );


        $this->showCustomIconsEditing(1, $form, false);

        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTitleIconVisibility();

        $form->addCommandButton("update", $this->lng->txt("save"));
        $form->addCommandButton("addTranslation", $this->lng->txt("add_translation"));

        return $form;
    }

    protected function getEditFormValues() : array
    {
        // values are set in initEditForm()
        return [];
    }

    public function updateObject() : void
    {
        global $ilSetting;

        $obj_service = $this->getObjectService();

        if (!$this->checkPermissionBool("write")) {
            throw new ilPermissionException($this->lng->txt("msg_no_perm_write"));
        }

        $form = $this->initEditForm();
        if ($form->checkInput()) {
            $this->saveSortingSettings($form);

            // list presentation
            $this->saveListPresentation($form);

            if ($ilSetting->get('custom_icons')) {
                global $DIC;
                /** @var ilObjectCustomIconFactory $customIconFactory */
                $customIconFactory = $DIC['object.customicons.factory'];
                $customIcon = $customIconFactory->getByObjId($this->object->getId(), $this->object->getType());

                /** @var ilImageFileInputGUI $item */
                $fileData = (array) $form->getInput('cont_icon');
                $item = $form->getItemByPostVar('cont_icon');

                if ($item->getDeletionFlag()) {
                    $customIcon->remove();
                }

                if ($fileData['tmp_name']) {
                    $customIcon->saveFromHttpRequest();
                }
            }

            // custom icon
            $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTitleIconVisibility();

            // BEGIN ChangeEvent: Record update
            global $ilUser;
            ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
            ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
            // END ChangeEvent: Record update

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "edit");
        }

        // display form to correct errors
        $this->setEditTabs();
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    public static function _goto(string $a_target) : void
    {
        ilObjectGUI::_gotoRepositoryRoot(true);
    }
}
