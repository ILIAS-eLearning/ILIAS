<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Bibliographic Administration Settings.
 *
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilPermissionGUI, ilObjBibliographicAdminLibrariesGUI
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilBiblAdminFieldGUI
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilBiblLibraryGUI
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilBiblAdminRisFieldGUI, ilBiblAdminBibtexFieldGUI
 */
class ilObjBibliographicAdminGUI extends ilObjectGUI
{
    const TAB_FIELDS = 'fields';
    const TAB_SETTINGS = 'settings';
    const CMD_DEFAULT = 'view';
    /**
     * @var string this is the ILIAS-type, not the Bib-type
     */
    protected $type = 'bibs';
    /**
     * @var ilObjBibliographicAdmin
     */
    public $object;
    /**
     * @var \ilBiblAdminFactoryFacadeInterface
     */
    protected $facade;


    /**
     * ilObjBibliographicAdminGUI constructor.
     *
     * @param      $a_data
     * @param      $a_id
     * @param bool $a_call_by_reference
     * @param bool $a_prepare_output
     *
     * @throws \ilObjectException
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->type = 'bibs';
        $this->lng->loadLanguageModule('bibl');
        // Check Permissions globally for all SubGUIs. We check read-permission first
        $this->checkPermission('read');
    }


    /**
     * @return bool|void$
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case strtolower(ilBiblLibraryGUI::class):
                $this->prepareOutput();
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $f = new ilBiblAdminLibraryFacade($this->object);
                $this->ctrl->forwardCommand(new ilBiblLibraryGUI($f));
                break;
            case strtolower(ilBiblAdminRisFieldGUI::class):
                $this->prepareOutput();
                $this->tabs_gui->activateTab(self::TAB_FIELDS);
                $this->ctrl->forwardCommand(new ilBiblAdminRisFieldGUI(new ilBiblAdminFactoryFacade($this->object, ilBiblTypeFactoryInterface::DATA_TYPE_RIS)));
                break;
            case strtolower(ilBiblAdminBibtexFieldGUI::class):
                $this->prepareOutput();
                $this->tabs_gui->activateTab(self::TAB_FIELDS);
                $this->ctrl->forwardCommand(new ilBiblAdminBibtexFieldGUI(new ilBiblAdminFactoryFacade($this->object, ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX)));
                break;
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT);
                $this->{$cmd}();
                break;
        }
    }


    protected function view()
    {
        $this->ctrl->redirectByClass(ilBiblAdminRisFieldGUI::class);
    }


    public function getAdminTabs()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        /**
         * @var $rbacsystem ilRbacSystem
         */
        if ($rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->tabs_gui->addTab('fields', $this->lng->txt('fields'), $this->ctrl->getLinkTargetByClass(array(
                ilObjBibliographicAdminGUI::class,
                ilBiblAdminRisFieldGUI::class,
            ), ilBiblAdminRisFieldGUI::CMD_STANDARD));
        }

        if ($rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTab(self::TAB_SETTINGS, $this->lng->txt('settings'), $this->ctrl->getLinkTargetByClass(array(
                ilObjBibliographicAdminGUI::class,
                ilBiblLibraryGUI::class,
            ), ilBiblLibraryGUI::CMD_INDEX));
        }
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), array(), 'ilpermissiongui');
        }
    }


    /**
     * @return \ilTabsGUI
     */
    public function getTabsGui()
    {
        return $this->tabs_gui;
    }


    /**
     * @param \ilTabsGUI $tabs_gui
     */
    public function setTabsGui($tabs_gui)
    {
        $this->tabs_gui = $tabs_gui;
    }
}
