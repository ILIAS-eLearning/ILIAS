<?php

/**
 * Class ilObjMainMenuGUI
 *
 * @ilCtrl_IsCalledBy ilObjMainMenuGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjMainMenuGUI: ilPermissionGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenuGUI extends ilObject2GUI
{

    /**
     * @var ilMMTabHandling
     */
    private $tab_handling;
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilLanguage
     */
    public $lng;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
     */
    public $tpl;
    /**
     * @var ilTree
     */
    public $tree;
    const TAB_PERMISSIONS = 'perm_settings';
    const TAB_MAIN = 'main';


    /**
     * ilObjMainMenuGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $ref_id = (int) $_GET['ref_id'];
        parent::__construct($ref_id);

        $this->tabs = $DIC['ilTabs'];
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('mme');
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->tab_handling = new ilMMTabHandling($ref_id);

        $this->assignObject();
    }


    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        if ($next_class == '') {
            $this->ctrl->redirectByClass(ilMMTopItemGUI::class);

            return;
        }

        $this->prepareOutput();

        switch ($next_class) {
            case strtolower(ilPermissionGUI::class):
                $this->tab_handling->initTabs(self::TAB_PERMISSIONS);
                $this->tabs->activateTab(self::TAB_PERMISSIONS);
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case strtolower(ilMMTopItemGUI::class):
                $g = new ilMMTopItemGUI($this->tab_handling);
                $this->ctrl->forwardCommand($g);
                break;
            case strtolower(ilMMSubItemGUI::class):
                $g = new ilMMSubItemGUI($this->tab_handling);
                $this->ctrl->forwardCommand($g);
                break;
            default:
                break;
        }
    }


    /**
     * @inheritDoc
     */
    public function getType()
    {
        return null;
    }
}
